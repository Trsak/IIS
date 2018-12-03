<?php

declare(strict_types=1);

namespace App\Model;

use DateTime;
use Nette;

final class LiteratureManager
{
    use Nette\SmartObject;

    private const
        LITERATURE_TABLE = 'literature',
        LITERATURE_COLUMN_ID = 'id',
        LITERATURE_COLUMN_ISBN = 'isbn',
        LITERATURE_COLUMN_PUBLISHER = 'publisher',
        LITERATURE_COLUMN_TITLE = 'title',
        LITERATURE_COLUMN_SUBTITLE = 'subtitle',
        LITERATURE_COLUMN_PUBLICATION_DATE = 'date',
        LITERATURE_COLUMN_PAGES = 'pages_number',
        LITERATURE_COLUMN_DESCRIPTION = 'description',
        LITERATURE_COLUMN_PIECES_TOTAL = 'pieces_total',
        LITERATURE_COLUMN_PIECES_BORROWED = 'pieces_borrowed';

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Updates literature
     * @param int $id
     * @throws LiteratureAddException
     */
    public function updateLiterature($id, $values): void
    {
        $row = $this->database->table(self::LITERATURE_TABLE)
            ->where(self::LITERATURE_COLUMN_ID, $id)
            ->fetch();

        if (!$row) {
            throw new LiteratureAddException("Literature with given id does not exist!");
        }

        if ($row["pieces_borrowed"] > $values->pieces_total) {
            throw new LiteratureAddException("There would be more borrowed pieces then available!");
        }

        $publication_date = DateTime::createFromFormat('d/m/Y', "00/00/0000");
        if ($values->publication_date) {
            $publication_date = DateTime::createFromFormat('d/m/Y', $values->publication_date);
        }

        $this->database->query('UPDATE literature SET', [
            'title' => $values->title,
            'subtitle' => $values->subtitle,
            'publisher' => $values->publisher,
            'publication_date' => $publication_date->format('Y-m-d'),
            'pages_number' => $values->pages_number,
            'description' => $values->description,
            'pieces_total' => $values->pieces_total,
            'image' => $values->image,
        ], 'WHERE id = ?', $id);
    }

    /**
     * Adds new literature.
     * @param \Scriptotek\GoogleBooks\Volume $volume
     * @throws LiteratureAddException
     */
    public function add($volume, array $data): void
    {
        [$isbn, $pieces_total] = $data;

        if ($volume == null) {
            throw new LiteratureAddException("No literature was found with given ISBN.");
        }

        $link = null;

        if (isset($volume->imageLinks->thumbnail)) {
            $link = $volume->imageLinks->thumbnail;
        }
        try {
            $this->database->query('INSERT INTO ' . self::LITERATURE_TABLE, [
                'isbn' => $isbn,
                'publisher' => ($volume->publisher == null) ? "Unknown" : $volume->publisher,
                'title' => $volume->title,
                'subtitle' => $volume->subtitle,
                'publication_date' => $volume->publishedDate,
                'pages_number' => ($volume->pageCount == null) ? "0" : $volume->pageCount,
                'description' => ($volume->description == null) ? "" : $volume->description,
                'pieces_total' => $pieces_total,
                'image' => $link,
            ]);

            $literatureId = $this->database->getInsertId();

            foreach ($volume->authors as $author) {
                $this->database->query('INSERT INTO literature_has_author', [
                    'literature_id' => $literatureId,
                    'author' => $author,
                ]);
            }
        } catch (Nette\Database\NotNullConstraintViolationException $e) {
            throw new LiteratureAddException("Given literature is not valid.");
        }
    }


    /**
     * Adds new literature.
     * @param $values
     * @throws LiteratureAddException
     */
    public function addBorrowing($values): void
    {
        try {
            $literature = $this->database->table(self::LITERATURE_TABLE)
                ->where(self::LITERATURE_COLUMN_ID, $values->literature)
                ->fetch();

            if (!$literature) {
                throw new LiteratureAddException("Literature with given id does not exist!");
            }

            $borrowing = $this->database->table("borrowing")
                ->where("user_id", $values->user)
                ->where("literature_id", $values->literature)
                ->where("return_date IS NULL")
                ->fetch();

            if ($borrowing) {
                throw new LiteratureAddException("This literature is already borrowed by user!");
            }

            if ($literature["pieces_borrowed"] + 1 > $literature["pieces_total"]) {
                throw new LiteratureAddException("There is not enough pieces of selected literature!");
            }

            $now = new DateTime();
            $return_until = DateTime::createFromFormat('d/m/Y', $values->return_until);
            $this->database->query('INSERT INTO borrowing', [
                'user_id' => $values->user,
                'literature_id' => $values->literature,
                'borrowing_date' => $now->format('Y-m-d'),
                'return_until_date' => $return_until->format('Y-m-d'),
            ]);

            $this->database->query('UPDATE literature SET ', [
                'pieces_borrowed+=' => 1,
            ], 'WHERE id = ?', $values->literature);
        } catch (Nette\Database\NotNullConstraintViolationException $e) {
            throw new LiteratureAddException("Given literature is not valid.");
        }
    }
}

class LiteratureAddException extends \Exception
{
}