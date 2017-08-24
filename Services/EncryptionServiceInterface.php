<?php
namespace Employees\Services;

interface EncryptionServiceInterface
{
    public function hash(string $password):string ;

    public function verify(string $password, string $hash) : bool;
}