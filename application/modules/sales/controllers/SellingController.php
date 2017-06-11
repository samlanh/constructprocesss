<?php
class Sales_SellingController extends Zend_Controller_Action
{	
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    } 
   	public function indexAction()
	{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
					'branch_id'=>-1,
					'customer_id'=>-1,
					);
		}
		$db = new Sales_Model_DbTable_DbSaleOrder();
		$rows = $db->getAllSaleOrder($search);
		$columns=array("Com.Name","CON_NAME","SALE_AGENT","SALE_ORDER","Project Name","Duration","Project Type",
				"TOTAL","DISCOUNT","TOTAL_AMOUNT","DATE","APPROVED_STATUS","PENDING_STATUS","BY_USER");
		$link=array(
				'module'=>'sales','controller'=>'index','action'=>'edit',
		);
		$link1=array(
				'module'=>'sales','controller'=>'index','action'=>'viewapp',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('contact_name'=>$link,'branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,
				'sale_no'=>$link,'approval'=>$link1));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
		
	}
	function addAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Sales_Model_DbTable_DbSaleOrder();
				if(!empty($data['identity'])){
					$dbq->addNewBOQ($data);
				}
				Application_Form_FrmMessage::message("INSERT_SUCESS");
				if(!empty($data['btnsavenew'])){
					Application_Form_FrmMessage::redirectUrl("/sales/quoatation");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}

		///link left not yet get from DbpurchaseOrder
		$frm_purchase = new Sales_Form_FrmSale(null);
		$form_sale = $frm_purchase->SaleOrder(null);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
			
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$this->view->term_opt = $db->getAllTermCondition(1);
		$this->view->sale_term_defual = $db->getAllTermCondition(null,2,1);
		
		$formpopup = new Sales_Form_FrmCustomer(null);
		$formpopup = $formpopup->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		
		$this->view->userinfo = $this->GetuserInfoAction();
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Sales_Model_DbTable_DbSaleOrder();
		$this->view->rscost = $db->getAllCostpriceById($id);
		
	}
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Sales_Model_DbTable_Dbquoatation();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				if(!empty($data['identity'])){
					$dbq->updateQoutation($data);
				}else{
					Application_Form_FrmMessage::message('No Data to Submit');
				}
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/quoatation");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $dbq->getQuotationItemById($id);
		
		if($row['is_approved']==1){
			if($row['is_approved']==1){
				$urs = $this->GetuserInfoAction();
				if($urs['level']!=1){
					Application_Form_FrmMessage::Sucessfull("QUOTATIO_WARNING","/sales/quoatation");
				}
			}
		}
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/sales/quoatation");
		}		
		$this->view->rs = $dbq->getQuotationItemDetailid($id);
		$this->view->rsterm = $dbq->getTermconditionByid($id);
		$this->view->rsq = $row;
		$frm_purchase = new Sales_Form_FrmQuoatation();
		$form_sale = $frm_purchase->SaleOrder($row);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		
		$db = new Application_Model_GlobalClass();
		$this->view->items = $db->getProductOption();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->term_opt = $db->getAllTermCondition(1);
		
		$formpopup = new Sales_Form_FrmCustomer(null);
		$formpopup = $formpopup->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
	}	
	public function quotadetailAction(){
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$dbq = new Sales_Model_DbTable_Dbquoatation();
			try {
			    $dbq->RejectQuotation($data);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/quoatation");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/quoatation");
		}
		$query = new report_Model_DbQuery();
		$this->view->product =  $query->getQuotationById($id);
		$rs = $query->getQuotationById($id);
		if(empty($rs)){
			$this->_redirect("/sales/quoatation");
		}
		$db= new Application_Model_DbTable_DbGlobal();
		$this->view->rscondition = $db->getTermConditionById(1, $id);
	}
	function getquotenoAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$qo = $db->getQuoationNumber($post['branch_id']);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
	function getprojectcostAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbSaleOrder();
			$qo = $db->getAllProjectCost($post['project_id']);
			echo Zend_Json::encode($qo);
			exit();
		}	
	}
}