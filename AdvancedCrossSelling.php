<?php
/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdvancedCrossSelling extends Module
{
    protected $config_form = false;



    //Definition des menus
    protected $tabs = [
        [
            'name'      => 'Analytics CrossSelling',
            'className' => 'Analytics',
            'active'    => 1,
            //submenus
            /*           'childs'    => [
                            [
                                'active'    => 1,
                                'name'      => 'Exportation',
                                'className' => 'ExportOrders',
                            ],
                        ], */
        ],
    ];

    public function __construct()
    {
        /*TODO TAB
                Add the module to the Quick Acces on the right bar
            */
        $this->name = 'AdvancedCrossSelling';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Issam aboulwafi';
        $this->need_instance = 0;


        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Advanced Cross Selling');
        $this->description = $this->l('cross selling module');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        $this->addTab($this->tabs);
        
        if (!parent::install() ||

            !$this->registerHook('header') ||
            !$this->registerHook('shoppingCart') ||
            !$this->registerHook('displayBackOfficeHeader')||
            !Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS')||
            !Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_STATIC')||
            !Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_DYNAMIQUE')||
            !Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_ICM')||
            !Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_ICN')||
            !Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_BACKGROUND')||
            !Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_MSG')



        ) {
            return false;
        }

        $this->_clearCache('cross.tpl');
        Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_BEST_SELLER',0);
        return true;
    }





    public function uninstall()
    {


        $this->_clearCache('cross.tpl');

            Configuration::deleteByName('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS');
            Configuration::deleteByName('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_STATIC');
            Configuration::deleteByName('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_DYNAMIQUE');
            Configuration::deleteByName('ADVANCEDCROSSSELLING_DISPLAY_BEST_SELLER');
            Configuration::deleteByName('ADVANCEDCROSSSELLING_DISPLAY_ICM');
            Configuration::deleteByName('ADVANCEDCROSSSELLING_DISPLAY_ICN');
            Configuration::deleteByName('ADVANCEDCROSSSELLING_DISPLAY_BACKGROUND');
            Configuration::deleteByName('ADVANCEDCROSSSELLING_DISPLAY_MSG');
     return parent::uninstall() && $this->removeTab($this->tabs);
    }






    public function addTab(
        $tabs,
        $id_parent = 0
    )
    {
        foreach ($tabs as $tab)
        {
            $tabModel             = new Tab();
            $tabModel->module     = $this->name;
            $tabModel->active     = $tab['active'];
            $tabModel->class_name = $tab['className'];
            $tabModel->id_parent  = $id_parent;

            //tab text in each language
            foreach (Language::getLanguages(true) as $lang)
            {
                $tabModel->name[$lang['id_lang']] = $tab['name'];
            }

            $tabModel->add();

            //submenus of the tab
            if (isset($tab['childs']) && is_array($tab['childs']))
            {
                $this->addTab($tab['childs'], Tab::getIdFromClassName($tab['className']));
            }
        }
        return true;
    }

    public function removeTab($tabs)
    {
        foreach ($tabs as $tab)
        {
            $id_tab = (int) Tab::getIdFromClassName($tab["className"]);
            if ($id_tab)
            {
                $tabModel = new Tab($id_tab);
                $tabModel->delete();
            }

            if (isset($tab["childs"]) && is_array($tab["childs"]))
            {
                $this->removeTab($tab["childs"]);
            }
        }

        return true;
    }




    public function setMedia()
	{
	  parent::setMedia();
	  $this->addJs(dirname(__FILE__).'/../../js/jquery-te-1.4.0.min.js');

	}

    public function hookShoppingCart()
    {
        $background = Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_BACKGROUND');


        /* STATIC CONTENT */
        if(Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_STATIC')){
        
            $this->_clearCache('cross.tpl');
            $reference = Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS');
            $ICM = Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_ICM');
            $ICN = Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_ICN');





            if( $reference != ""){


                    $references = explode(",", $reference);

                    $result=array();
                    $array_image = array();
                    if (!Validate::isReference($reference))
                        die(Tools::displayError());

                    $sql ="SELECT p.id_product,(pa.price +(pa.price*tax.rate)/100) AS price,p.reference,pl.description_short,pl.name 
                    FROM "._DB_PREFIX_."product AS p
                    JOIN "._DB_PREFIX_."tax AS tax
                    LEFT JOIN "._DB_PREFIX_."product_lang AS pl ON p.id_product = pl.id_product 
                    LEFT JOIN "._DB_PREFIX_."product_attribute AS pa ON p.id_product =pa.id_product

                    WHERE p.reference IN (";


                    for ($i=0;$i<count($references);$i++){
                        $sql.="'".$references[$i]."',";

                    }
                    $sql=substr($sql,0,-1);
                    $sql.= ") GROUP BY p.id_product LIMIT 3";
                    $result = Db::getInstance()->ExecuteS($sql);



                    

                    global $link;

                    for ($i=0;$i<count($result);$i++  ) {
                        $id_image = Product::getCover($result[$i]["id_product"]);
                        // get Image by id
                        if (sizeof($id_image) > 0) {
                            $image = new Image($id_image['id_image']);
                            // get image full URL
                            $image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";

                            $result[$i]['url_image'] =$image_url;

                            // get product link
                            $link_p = $link->getProductLink($result[$i]["id_product"]);
                            $result[$i]['url_product'] =$link_p."?icm=".$ICM."&icn=".$ICN;


                        }


                    }
                    $this->context->smarty->assign('results',$result);
                    $this->context->smarty->assign('background',$background);
                    $this->context->smarty->assign('message',Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_MSG'));
                    return $this->display(__FILE__,'views/templates/admin/cross.tpl');

                }




        }
            /* DYNAMIQUE CONTENT */


       else if(Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_DYNAMIQUE')){

           $nbr = Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_BEST_SELLER');
           $ICM = Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_ICM');
           $ICN = Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_ICN');


           $this->_clearCache('cross.tpl');


               $result=array();
               $array_image = array();

               /* current cart products */
                $products = Context::getContext()->cart->getProducts();   
                
                if(!empty($products)){
                $product_category = array();
                $product_reference = array();
                foreach ($products as $product) {
                $product_category[] = (int)$product['id_category_default'];
                $product_reference[]= $product['reference'];

                }


            /*
             *
             * $sql ="SELECT p.id_product,(pa.price +(pa.price*tax.rate)/100) AS price,p.reference,pl.description_short,pl.name
                    FROM "._DB_PREFIX_."product AS p
                    JOIN "._DB_PREFIX_."tax AS tax
             *
             * */




                    $sql ="SELECT p.id_product,(pa.price +(pa.price*tax.rate)/100) AS price,p.reference,pl.description_short,pl.name
                            FROM "._DB_PREFIX_."product AS p
                            JOIN "._DB_PREFIX_."tax AS tax
                            LEFT JOIN "._DB_PREFIX_."product_lang AS pl ON p.id_product = pl.id_product
                            LEFT JOIN  "._DB_PREFIX_."product_sale AS psale ON p.id_product = psale.id_product
                            LEFT JOIN "._DB_PREFIX_."product_attribute AS pa ON p.id_product =pa.id_product
                            WHERE psale.sale_nbr >= ".$nbr."  AND p.id_category_default IN (";


                    for ($i=0;$i<count($product_category);$i++)
                    $sql.="'".$product_category[$i]."',";              
                    $sql=substr($sql,0,-1);
    
    
                    $sql.= ") AND p.reference NOT IN (";

                    for ($i=0;$i<count($product_reference);$i++)
                    $sql.="'".$product_reference[$i]."',";              
                    $sql=substr($sql,0,-1);
    
    
                    $sql.= ") GROUP BY p.id_product  LIMIT 3 ";
                    $result = Db::getInstance()->ExecuteS($sql); 


                    global $link;
    
                    for ($i=0;$i<count($result);$i++  ) {
                            $id_image = Product::getCover($result[$i]["id_product"]);
                            // get Image by id
                            if (sizeof($id_image) > 0) {
                                $image = new Image($id_image['id_image']);
                                // get image full URL
                                $image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
                            
                                $result[$i]['url_image'] =$image_url;
                                
                                // get product link
                                $link_p = $link->getProductLink($result[$i]["id_product"]);
                                $result[$i]['url_product'] =$link_p."?icm=".$ICM."&icn=".$ICN;
    
                             }
                    
    
                      }


                      $this->context->smarty->assign('results',$result);
                      $this->context->smarty->assign('background',$background);
                      $this->context->smarty->assign('message',Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_MSG'));

                      return $this->display(__FILE__,'views/templates/admin/cross.tpl');


            }
        }
    }


    /**
     * Load the configuration form
     
     */
    public function getContent()
    {

       $this->html = '';

       if (Tools::isSubmit('submitAdvancedCrossSellingModule')) {

               Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS',Tools::getValue('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS'));
               Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_STATIC',Tools::getValue('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_STATIC'));
               Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_DYNAMIQUE',Tools::getValue('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_DYNAMIQUE'));
               Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_BEST_SELLER',Tools::getValue('ADVANCEDCROSSSELLING_DISPLAY_BEST_SELLER'));
               Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_ICM',Tools::getValue('ADVANCEDCROSSSELLING_DISPLAY_ICM'));
               Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_ICN',Tools::getValue('ADVANCEDCROSSSELLING_DISPLAY_ICN'));
               Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_BACKGROUND',Tools::getValue('ADVANCEDCROSSSELLING_DISPLAY_BACKGROUND'));
               Configuration::updateValue('ADVANCEDCROSSSELLING_DISPLAY_MSG',Tools::getValue('ADVANCEDCROSSSELLING_DISPLAY_MSG'));



               $this->_clearCache('cross.tpl');
               $this->html .= $this->displayConfirmation($this->l('Settings updated successfully'));

       }

       $this->html .= $this->renderForm();
       $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
       return $this->html;
   }

   /**
    * Create the form that will be displayed in the configuration of your module.
    */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAdvancedCrossSellingModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }
    /**
     * Create the structure of your form.
     */

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),


                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('STATIC MODE'),
                        'name' => 'ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_STATIC',
                        
                        'is_bool' => true,
                        'desc' => $this->l('USE THIS MODE IN ORDER TO ADD STATIC PRODUCTS TO ORDER PAGE CROSS SELLING'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 5,
                        'id' => 'products',
                        'type' => 'text',
                        
                        'prefix' => '<i class="icon icon-barcode"></i>',
                        'desc' => $this->l('Enter products  reference separated with  , (only 3 products )'),
                        'name' => 'ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS',
                        'label' => $this->l('Reference'),
                    ),


                    array(
                        'type' => 'switch',
                        'label' => $this->l('DYNAMIQUE MODE'),
                        'name' => 'ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_DYNAMIQUE',
                        'is_bool' => true,
                        'desc' => $this->l('USE THIS MODE IN ORDER TO ADD DYNAMIQUE PRODUCTS TO ORDER PAGE CROSS SELLING'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),

                    array(
                        'col' => 5,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-shopping-cart"></i>',
                        'desc' => $this->l('ENTER THE QUANTITY OF MAX BEST SELLER PRODUCT'),
                        'name' => 'ADVANCEDCROSSSELLING_DISPLAY_BEST_SELLER',
                        'label' => $this->l('Quantity'),
                    ),

                    array(
                        'col' => 5,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-signal"></i>',
                        'desc' => $this->l('ENTER THE ICM TAG'),
                        'name' => 'ADVANCEDCROSSSELLING_DISPLAY_ICM',
                        'label' => $this->l('ICM'),
                    ),

                    array(
                        'col' => 5,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-signal"></i>',
                        'desc' => $this->l('ENTER THE ICN TAG'),
                        'name' => 'ADVANCEDCROSSSELLING_DISPLAY_ICN',
                        'label' => $this->l('ICN'),
                    ),



                    array(
                        'col' => 5,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-tag"></i>',
                        'desc' => $this->l('ENTER THE COLOR FOR BACKGROUND'),
                        'name' => 'ADVANCEDCROSSSELLING_DISPLAY_BACKGROUND',
                        'label' => $this->l('BACKGROUND'),
                    ),
                    array(
                        'col' => 5,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-tag"></i>',
                        'desc' => $this->l('ENTER THE CROSS SELLING MSG'),
                        'name' => 'ADVANCEDCROSSSELLING_DISPLAY_MSG',
                        'label' => $this->l('Message'),
                    ),

                ),



                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS' => Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS', ""),
            'ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_STATIC' => Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_STATIC', false),
            'ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_DYNAMIQUE' => Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_PRODUCTS_DYNAMIQUE', false),
            'ADVANCEDCROSSSELLING_DISPLAY_BEST_SELLER' => Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_BEST_SELLER'),
            'ADVANCEDCROSSSELLING_DISPLAY_ICM' => Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_ICM'),
            'ADVANCEDCROSSSELLING_DISPLAY_ICN' => Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_ICN'),
            'ADVANCEDCROSSSELLING_DISPLAY_BACKGROUND' => Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_BACKGROUND'),
            'ADVANCEDCROSSSELLING_DISPLAY_MSG' => Configuration::get('ADVANCEDCROSSSELLING_DISPLAY_MSG'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJquery(); 
            $this->context->controller->addJS($this->_path.'/views/js/back.js');
            $this->context->controller->addCSS($this->_path.'/views/css/back.css');
        }
    }



    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
}
