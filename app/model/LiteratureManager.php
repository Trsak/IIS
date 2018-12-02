<?php

declare(strict_types=1);

namespace App\Model;

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

            foreach($volume->authors as $author) {
                $this->database->query('INSERT INTO literature_has_author', [
                    'literature_id' => $literatureId,
                    'author' => $author,
                ]);
            }
        } catch (Nette\Database\NotNullConstraintViolationException $e) {
            throw new LiteratureAddException("Given literature is not valid.");
        }
    }
}

class LiteratureAddException extends \Exception
{
}