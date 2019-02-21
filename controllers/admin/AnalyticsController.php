<?php
/**
 * Created by PhpStorm.
 * User: Issam
 * Date: 26/10/2018
 * Time: 17:38
 */
if(!defined('_PS_VERSION_'))
    exit();
class AnalyticsController extends AdminController
{
    public function __construct(){
        $this->bootstrap = true;
        parent::__construct();

    }

    public function getTemplatePath()
    {
        return dirname(__FILE__).'/../../views/templates/admin/';
    }

    public function createTemplate($tpl_name) {
        if (file_exists($this->getTemplatePath() . $tpl_name) && $this->viewAccess())
            return $this->context->smarty->createTemplate($this->getTemplatePath() . $tpl_name, $this->context->smarty);
        return parent::createTemplate($tpl_name);
    }


    public function initContent(){
        $this->content=$this->createTemplate('Analytics.tpl')->fetch();
        parent::initContent();


    }

    public function setMedia(){
        parent::setMedia();
    }


}
