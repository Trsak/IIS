<?php

declare(strict_types=1);

namespace App\Model;

use DateTime;
use Nette;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Security\Passwords;

final class UserManager implements Nette\Security\IAuthenticator
{
    use Nette\SmartObject;

    private const
        TABLE_NAME = 'user',
        COLUMN_ID = 'id',
        COLUMN_FIRST_NAME = 'name',
        COLUMN_LAST_NAME = 'last_name',
        COLUMN_EMAIL = 'email',
        COLUMN_PASSWORD_HASH = 'password',
        COLUMN_PHONE = 'telephone',
        COLUMN_BIRTHDATE = 'birthdate',
        COLUMN_MEMBERSHIP_EXPIRATION = 'member_until',
        COLUMN_IS_EMPLOYEE = 'employee';

    /** @var Nette\Database\Context */
    private $database;

    /** @var Passwords */
    private $passwords;

    public function __construct(Nette\Database\Context $database, Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    /**
     * Performs an authentication.
     * @param array $credentials
     * @return Nette\Security\IIdentity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials): Nette\Security\IIdentity
    {
        [$email, $password] = $credentials;

        $row = $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_EMAIL, $email)
            ->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('Member with this email does not exist.', self::IDENTITY_NOT_FOUND);

        } elseif (!$this->passwords->verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

        } elseif ($this->passwords->needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
            $row->update([
                self::COLUMN_PASSWORD_HASH => $this->passwords->hash($password),
            ]);
        }

        $arr = $row->toArray();
        unset($arr[self::COLUMN_PASSWORD_HASH]);
        return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_IS_EMPLOYEE], $arr);
    }

    /**
     * Adds new member.
     * @param $data
     * @throws UserException
     */
    public function add($data): void
    {
        $member_untilDate = DateTime::createFromFormat('d/m/Y', $data->member_until);
        if (!$member_untilDate) {
            throw new UserException("Wrong membership until date format!");
        }

        $member_birthdate = DateTime::createFromFormat('d/m/Y', "00/00/0000");
        if ($data->birthdate) {
            $member_birthdate = DateTime::createFromFormat('d/m/Y', $data->birthdate);
            if (!$member_birthdate) {
                throw new UserException("Wrong birthdate format!");
            }
        }

        try {
            $user = $this->database->table(self::TABLE_NAME)->insert([
                self::COLUMN_FIRST_NAME => $data->name,
                self::COLUMN_LAST_NAME => $data->last_name,
                self::COLUMN_EMAIL => $data->email,
                self::COLUMN_PHONE => $data->telephone,
                self::COLUMN_BIRTHDATE => $member_birthdate->format('Y-m-d'),
                self::COLUMN_MEMBERSHIP_EXPIRATION => $member_untilDate->format('Y-m-d'),
                self::COLUMN_PASSWORD_HASH => "",
            ]);

            self::generateNewPassword($user->id);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new UserException("Member with this email already exists!");
        }
    }

    /**
     * Generates new password and sends it to member email.
     * @param int $id
     * @throws UserException
     */
    public function generateNewPassword($id): void
    {
        $row = $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_ID, $id)
            ->fetch();

        if (!$row) {
            throw new UserException("Member with given id does not exist!");
        }

        $password = self::randomPassword();
        $passwords = new Passwords();

        $this->database->query('UPDATE user SET', [
            'password' => $passwords->hash($password)
        ], 'WHERE id = ?', $id);

        $mail = new Message;
        $mail->setFrom('iLibrary <xsopfp00@stud.fit.vutbr.cz>')
            ->addTo($row["email"])
            ->setSubject('iLibrary - New password')
            ->setHtmlBody("Hello,<br>new password has been set for your account.<br><strong>Password:</strong> " . $password);
        $mailer = new SendmailMailer;
        $mailer->send($mail);
    }

    private function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
}

class UserException extends \Exception
{
}