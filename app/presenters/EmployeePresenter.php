<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\LiteratureAddException;
use App\Model\LiteratureManager;
use App\Model\UserException;
use App\Model\UserManager;
use DateTime;
use Nette\Application\UI\Form;
use Scriptotek\GoogleBooks\GoogleBooks;
use Nette;

final class EmployeePresenter extends SecureEmployeePresenter
{
    private $books;

    private $literatureManager;

    /** @var UserManager */
    private $userManager;

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database, GoogleBooks $books, LiteratureManager $literatureManager, UserManager $userManager)
    {
        parent::__construct();
        $this->books = $books;
        $this->literatureManager = $literatureManager;
        $this->database = $database;
        $this->userManager = $userManager;
    }

    public function handleReturn($id, $literature): void
    {
        $now = new DateTime();
        $this->database->query('UPDATE borrowing SET', [
            'return_date' => $now->format('Y-m-d'),
        ], 'WHERE id = ?', $id);

        $this->database->query('UPDATE literature SET ', [
            'pieces_borrowed-=' => 1,
        ], 'WHERE id = ?', $literature);

        $this->redirect("Employee:Borrowings");
    }

    public function renderMembers(): void
    {
        $this->template->members = $this->database->table("user")
            ->fetchAll();
    }

    public function renderBorrowings(): void
    {
        $borrowings = $this->database->table("borrowing")->select("borrowing.*, user.name, user.last_name, user.email, literature.title, literature.subtitle")->joinWhere("user", "user.id = borrowing.user_id")->joinWhere("literature", "literature.id = borrowing.literature_id")->fetchAll();
        $this->template->borrowings = $borrowings;
    }

    public function renderLiterature(): void
    {
        $this->template->literatures = $this->database->table("literature")
            ->fetchAll();
    }

    public function renderEditLiteratures(int $id): void
    {
        $literature = $this->database->table("literature")->where("id = ?", $id)->fetch();
        if (!$literature) {
            $this->flashMessage("Literature was not found.", "Error");
            $this->redirect("Homepage:");
        }
        $this->template->literature = $literature;
    }

    public function renderEditMember(int $id): void
    {
        $member = $this->database->table("user")->where("id = ?", $id)->fetch();
        if (!$member) {
            $this->flashMessage("Member was not found.", "Error");
            $this->redirect("Homepage:");
        }
        $this->template->member = $member;
    }

    public function actionRemoveMember(int $id): void
    {
        $this->database->table('borrowing')
            ->where('user_id', $id)
            ->delete();

        $this->database->table('user')
            ->where('id', $id)
            ->delete();

        $this->flashMessage("Member was sucessfully deleted.", "Success");
        $this->redirect("Employee:Members");
    }

    public function actionRemove(int $id): void
    {
        $this->database->table('literature_has_author')
            ->where('literature_id', $id)
            ->delete();

        $this->database->table('literature')
            ->where('id', $id)
            ->delete();

        $this->flashMessage("Literature was sucessfully deleted.", "Success");
        $this->redirect("Employee:Literature");
    }

    protected function createComponentAddBorrowingForm(): Form
    {
        $users = $this->database->table("user")->select("id, name, last_name, email")
            ->fetchAll();

        $usersData = [];
        foreach ($users as $user) {
            $usersData[$user->id] = $user->name . " " . $user->last_name . " (" . $user->email . ")";
        }

        $literatures = $this->database->table("literature")->select("id, title, subtitle")
            ->fetchAll();

        $literaturesData = [];
        foreach ($literatures as $literature) {
            $literaturesData[$literature->id] = $literature->title;
            if ($literature->subtitle) {
                $literaturesData[$literature->id] = $literaturesData[$literature->id] . ": " . $literature->subtitle;
            }
        }

        $form = new Form;
        $form->addSelect('user', '', $usersData)
            ->setRequired("You have to select member!")
            ->setPrompt('Select member');
        $form->addSelect('literature', '', $literaturesData)
            ->setRequired("You have to select literature!")
            ->setPrompt('Select literature');
        $form->addText("return_until", null)
            ->setRequired('You have to set return until date!');
        $form->addSubmit("create", null);
        $form->onSuccess[] = [$this, 'addBorrowingFormSucceeded'];
        return $form;
    }

    public function addBorrowingFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->literatureManager->addBorrowing($values);
            $this->flashMessage("Borrowing was sucessfully extended.", "Success");
            $this->redirect("this");
        } catch (LiteratureAddException $e) {
            $this->flashMessage($e->getMessage(), "Error");
        }
    }

    protected function createComponentExtendMemberForm(): Form
    {
        $form = new Form;
        $form->addSelect('extendTime', '', [
            '1 month' => 'One month',
            '6 months' => 'Six months',
            '1 year' => 'One year',
        ])
            ->setRequired("You have to select extend time!");
        $form->addSubmit("extend", null);
        $form->onSuccess[] = [$this, 'extendMemberFormSucceeded'];
        return $form;
    }

    public function extendMemberFormSucceeded(Form $form, \stdClass $values): void
    {
        $params = $this->request->getParameters();
        if (isset($params["id"])) {
            try {
                $this->userManager->extendMembership($params["id"], $values->extendTime);
                $this->flashMessage("Membership was sucessfully extended.", "Success");
                $this->redirect("this");
            } catch (UserException $e) {
                $this->flashMessage($e->getMessage(), "Error");
            }
        }
    }

    protected function createComponentEditLiteratureForm(): Form
    {
        $form = new Form;
        $form->addText("title", null)
            ->setRequired('Please insert title.');
        $form->addText("subtitle", null);
        $form->addText("publisher", null)
            ->setRequired('Please insert publisher.');
        $form->addText("publication_date", null);
        $form->addText("pages_number", null)
            ->addRule(Form::INTEGER, 'Number of pages must be number!')
            ->setRequired('Please insert number of pages.');
        $form->addTextArea("description", null)
            ->setRequired('Please insert description.');
        $form->addText("image", null)
            ->addRule(Form::PATTERN, 'Image must be URL!', '(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&\'\(\)\*\+,;=.]+')
            ->setRequired(false);
        $form->addText("pieces_total", null)
            ->addRule(Form::INTEGER, 'Total pieces must be number!')
            ->setRequired('Please insert total pieces.');
        $form->addSubmit("edit", null);
        $form->onSuccess[] = [$this, 'editLiteratureFormSucceeded'];

        $params = $this->request->getParameters();
        if (isset($params["id"])) {
            $user = $this->database->table("literature")
                ->where("id = ?", $params["id"])
                ->fetch();

            $form->setDefaults([
                'title' => $user["title"],
                'subtitle' => $user["subtitle"],
                'publisher' => $user["publisher"],
                'pages_number' => $user["pages_number"],
                'pieces_total' => $user["pieces_total"],
                'description' => $user["description"],
                'image' => $user["image"],
            ]);

            if (strtotime($user["publication_date"]->format('d/m/Y')) > 0) {
                $form->setDefaults([
                    'publication_date' => $user["publication_date"]->format('d/m/Y')
                ]);
            }
        }

        return $form;
    }

    public function editLiteratureFormSucceeded(Form $form, \stdClass $values): void
    {
        $params = $this->request->getParameters();
        if (isset($params["id"])) {
            try {
                $this->literatureManager->updateLiterature($params["id"], $values);
                $this->flashMessage("Literature was sucessfully extended.", "Success");
                $this->redirect("this");
            } catch (UserException $e) {
                $this->flashMessage($e->getMessage(), "Error");
            }
        }
    }

    protected function createComponentEditMemberForm(): Form
    {
        $form = new Form;
        $form->addEmail("email", null)
            ->setRequired('Please insert member email.');
        $form->addText("name", null)
            ->setRequired('Please insert member name.');
        $form->addText("last_name", null)
            ->setRequired('Please insert member last name.');
        $form->addText("telephone", null);
        $form->addText("birthdate", null);
        $form->addSubmit("edit", null);
        $form->onSuccess[] = [$this, 'editMemberFormSucceeded'];

        $params = $this->request->getParameters();
        if (isset($params["id"])) {
            $user = $this->database->table("user")
                ->where("id = ?", $params["id"])
                ->fetch();

            $form->setDefaults([
                'email' => $user["email"],
                'name' => $user["name"],
                'last_name' => $user["last_name"],
                'telephone' => $user["telephone"]
            ]);

            if (strtotime($user["birthdate"]->format('d/m/Y')) > 0) {
                $form->setDefaults([
                    'birthdate' => $user["birthdate"]->format('d/m/Y')
                ]);
            }
        }

        return $form;
    }

    public function editMemberFormSucceeded(Form $form, \stdClass $values): void
    {
        $params = $this->request->getParameters();
        if (isset($params["id"])) {
            try {
                $this->userManager->updateMember($params["id"], $values);
                $this->flashMessage("Member sucessfully edited.", "Success");
                $this->redirect("this");
            } catch (UserException $e) {
                $this->flashMessage($e->getMessage(), "Error");
            }
        }
    }

    protected function createComponentAddMemberForm(): Form
    {
        $form = new Form;
        $form->addEmail("email", null)
            ->setRequired('Please insert member email.');
        $form->addText("name", null)
            ->setRequired('Please insert member name.');
        $form->addText("last_name", null)
            ->setRequired('Please insert member last name.');
        $form->addText("telephone", null);
        $form->addText("birthdate", null);
        $form->addText("member_until", null)
            ->setRequired('Please insert membership until date.');
        $form->addSubmit("add", null);
        $form->onSuccess[] = [$this, 'addMemberFormSucceeded'];
        return $form;
    }

    public function addMemberFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->userManager->add($values);
            $this->flashMessage("Member was sucessfully added.", "Success");
        } catch (UserException $e) {
            $this->flashMessage($e->getMessage(), "Error");
        }
        $this->redirect("Employee:Members");
    }

    protected function createComponentAddLiteratureForm(): Form
    {
        $form = new Form;
        $form->addText("isbn", null)
            ->setRequired('Please insert ISBN.')
            ->addRule(Form::PATTERN, 'Wrong ISBN format.', '(97(8|9))?\d{9}(\d|X)');
        $form->addText("pieces_total", null)
            ->setRequired('Please insert total pieces.')
            ->addRule(Form::INTEGER, 'Total pieces muset be an number.');
        $form->addSubmit("add", null);
        $form->onSuccess[] = [$this, 'addLiteratureFormSucceeded'];
        return $form;
    }

    public function addLiteratureFormSucceeded(Form $form, \stdClass $values): void
    {
        $volume = $this->books->volumes->byIsbn($values->isbn);
        try {
            $this->literatureManager->add($volume, [$values->isbn, $values->pieces_total]);
            $this->flashMessage("Literature was sucessfully added.", "Success");
        } catch (LiteratureAddException $e) {
            $this->flashMessage($e->getMessage(), "Error");
        }

        $this->redirect("Employee:Literature");
    }
}
