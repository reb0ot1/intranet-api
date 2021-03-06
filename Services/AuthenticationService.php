<?php

namespace Employees\Services;

use Employees\Adapter\DatabaseInterface;
use Employees\Core\MVC\KeyHolderInterface;
use Employees\Core\MVC\SessionInterface;


class AuthenticationService implements AuthenticationServiceInterface
{
    private $session;
    private $holder;
    private $db;

    public function __construct(SessionInterface $session, KeyHolderInterface $holder, DatabaseInterface $db)
    {
        $this->session = $session;
        $this->holder = $holder;
        $this->db = $db;
    }

    public function isAuthenticated() : bool
    {
        return $this->session->exists('id');
    }

    public function logout()
    {
       $this->session->destroy();
    }

    public function getUserId()
    {
        return $this->session->get('id');
    }

    private function isExpired($expireTime) {

        $now = time();

        if ($expireTime < $now) {
            return false;
        } else {
            return true;
        }
    }

    public function isTokenCorrect() : bool {

        $query = "SELECT * FROM admin_tokens WHERE token = ?";

        $stmt = $this->db->prepare($query);

        if ($stmt->execute([substr($this->holder->getTokenKey(),7)])) {

            $result = $stmt->fetch();
            if (is_array($result)) {
                if (array_key_exists('expire',$result)) {
                    return $this->isExpired($result['expire']);
                }
            }

        }

       return false;

    }

    public function getUserInfo()
    {
        $query = "SELECT admins.id, admins.first_name AS first, admins.last_name AS last FROM admins LEFT JOIN admin_tokens ON admins.id = admin_tokens.admin_id WHERE admin_tokens.token = ?";

        $stmt = $this->db->prepare($query);

        $stmt->execute([substr($this->holder->getTokenKey(),7)]);

        $result = $stmt->fetch();

        return $result;
    }



}