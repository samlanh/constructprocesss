<?php
class Product_indexController extends Zend_Controller_Action
{
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		// $db = new Application_Model_DbTable_DbGlobal();
		// $rs = $db->getValidUserUrl();
		// if(empty($rs)){
			// Application_Form_FrmMessage::Sucessfull("YOU_NO_PERMISION_TO_ACCESS_THIS_SECTION","/index/dashboad");
		// }
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
	function updatecodeAction(){
		$db = new Product_Model_DbTable_DbProduct();
		$db->getProductCoded();
	}
    public function indexAction()
    {
    	$db = new Product_Model_DbTable_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    			'branch'	=>	'',
    			'brand'		=>	'',
    			'category'	=>	'',
    			'model'		=>	'',
    			'color'		=>	'',
    			'size'		=>	'',
    			'status'	=>	1
    		);
    	}
		$columns=array("BRANCH","ITEM_CODE","ITEM_NAME", "BARCODE",
				"SERIAL_NO","TRADE_MARKE","CATEGORY","MODEL","MEASURE","QTY","STATUS");
		$link=array(
				'module'=>'product','controller'=>'index','action'=>'edit',
		);
		
		$rows = $db->getAllProduct($data);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch'=>$link,'item_code'=>$link,'item_name'=>$link,
				'barcode'=>$link));
				
    	$this->view->product = $db->getAllProduct($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
        
	}
	public function addAction()
	{
		$db = new Product_Model_DbTable_DbProduct();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->add($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/product/index');
					}elseif(isset($post["btn_print"])){
						
					}else{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/product/index");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$rs_branch = $db->getBranch();
			$this->view->branch = $rs_branch;
			
			$this->view->price_type = $db->getPriceType();
			
			$formProduct = new Product_Form_FrmProduct();
			$formStockAdd = $formProduct->add(null);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->form = $formStockAdd;
			
			$formBrand = new Product_Form_FrmBrand();
			$frmBrand = $formBrand->Brand();
			$this->view->frmBrand = $frmBrand;
			Application_Model_Decorator::removeAllDecorator($frmBrand);
			
			$formCat = new Product_Form_FrmCategory();
			$frmCat = $formCat->cat();
			$this->view->frmCat = $frmCat;
			Application_Model_Decorator::removeAllDecorator($frmCat);
			
			$formMeasure = new Product_Form_FrmMeasure();
			$frmMesure = $formMeasure->measure();
			$this->view->frmMesure = $frmMesure;
			Application_Model_Decorator::removeAllDecorator($frmMesure);
			
			$fmOther = new Product_Form_FrmOther();
			$frmOther = $fmOther->add();
			Application_Model_Decorator::removeAllDecorator($frmOther);
			$this->view->frmOther = $frmOther;
			
	}
	public function editAction()
	{
		$id = $this->getRequest()->getParam("id"); 
		$db = new Product_Model_DbTable_DbProduct();
		if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post["id"] = $id;
					$db->edit($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", '/product/index');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
		}
		$this->view->rs_location = $db->getProductLocation($id);
		$this->view->rs_price = $db->getProductPrcie($id);
		$rs = $db->getProductById($id);
		$formProduct = new Product_Form_FrmProduct();
		$formStockAdd = $formProduct->add($rs);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
		
		$formBrand = new Product_Form_FrmBrand();
			$frmBrand = $formBrand->Brand();
			$this->view->frmBrand = $frmBrand;
			Application_Model_Decorator::removeAllDecorator($frmBrand);
			
			$formCat = new Product_Form_FrmCategory();
			$frmCat = $formCat->cat();
			$this->view->frmCat = $frmCat;
			Application_Model_Decorator::removeAllDecorator($frmCat);
			
			$formMeasure = new Product_Form_FrmMeasure();
			$frmMesure = $formMeasure->measure();
			$this->view->frmMesure = $frmMesure;
			Application_Model_Decorator::removeAllDecorator($frmMesure);
			
			$fmOther = new Product_Form_FrmOther();
			$frmOther = $fmOther->add();
			Application_Model_Decorator::removeAllDecorator($frmOther);
			$this->view->frmOther = $frmOther;
	}
	
	public function addBrandAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$db = new Product_Model_DbTable_DbBrand();
				$brand_id =$db->addNew($post);
				$result = array('brand_id'=>$brand_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}
	public function addCategoryAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$db = new Product_Model_DbTable_DbCategory();
				$cat_id =$db->addNew($post);
				$result = array('cat_id'=>$cat_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}

	public function addMeasureAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$db = new Product_Model_DbTable_DbMeasure();
				$measure_id =$db->addNew($post);
				$result = array('measure_id'=>$measure_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}
	
	public function addOtherAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$db = new Product_Model_DbTable_DbOther();
				$other_id =$db->addNew($post);
				$result = array('other_id'=>$other_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}
	
	function outstockAction(){
		$db = new Product_Model_DbTable_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    			'branch'	=>	'',
    			'brand'		=>	'',
    			'category'	=>	'',
    			'model'		=>	'',
    			'color'		=>	'',
    			'size'		=>	'',
    			'status'	=>	1
    		);
    	}
    	$this->view->product = $db->getAllProductOutStock($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	function lowstockAction(){
		$db = new Product_Model_DbTable_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    			'branch'	=>	'',
    			'brand'		=>	'',
    			'category'	=>	'',
    			'model'		=>	'',
    			'color'		=>	'',
    			'size'		=>	'',
    			'status'	=>	1
    		);
    	}
    	$this->view->product = $db->getAllProductLowStock($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function getProductPrefixAction(){
		$post=$this->getRequest()->getPost();
		$get_code = new Product_Model_DbTable_DbProduct();
		$result = $get_code->getProductPrefix($post["id"]);
		echo Zend_Json::encode($result);
		exit();
	}
	
	
}

