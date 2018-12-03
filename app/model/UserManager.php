<?php

declare(strict_types=1);

namespace App\Model;

use DateTime;
use Nette;
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
            $this->database->table(self::TABLE_NAME)->insert([
                self::COLUMN_FIRST_NAME => $data->name,
                self::COLUMN_LAST_NAME => $data->last_name,
                self::COLUMN_EMAIL => $data->email,
                self::COLUMN_PHONE => $data->telephone,
                self::COLUMN_BIRTHDATE => $member_birthdate->format('Y-m-d'),
                self::COLUMN_MEMBERSHIP_EXPIRATION =>  $member_untilDate->format('Y-m-d'),
                self::COLUMN_PASSWORD_HASH => "",
            ]);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new UserException("Member with this email already exists!");
        }
    }
}

class UserException extends \Exception
{
}