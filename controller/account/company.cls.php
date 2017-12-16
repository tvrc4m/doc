<?php

class CompanyController extends BaseAuth{

    public function index(){

        $this->css[]='/static/css/login.css';
        $this->css[]='/static/css/certificate.css';

        $this->display('account/company.html');
    }
}