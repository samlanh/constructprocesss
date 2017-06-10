<?php
class Sales_IndexController extends Zend_Controller_Action
{	
	
    public function init()
    {
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
		$db = new Sales_Model_DbTable_Dbcost();
		$rows = $db->getAllSaleOrder($search);
		$columns=array("Com.Name","CON_NAME","SALE_AGENT","SALE_ORDER","Project Name","Duration","Project Type",
				"Total Labor Cost","Total Materail Cost","TOTAL_AMOUNT","DATE","APPROVED_STATUS","PENDING_STATUS","BY_USER");
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
				$dbq = new Sales_Model_DbTable_Dbcost();
				if(!empty($data['identity'])){
					$dbq->addNewSaleCost($data);
				}
				Application_Form_FrmMessage::message("INSERT_SUCESS");
				if(!empty($data['btnsavenew'])){
					Application_Form_FrmMessage::redirectUrl("/sales/index");
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
	}
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Sales_Model_DbTable_DbSaleOrder();
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				if(!empty($data['identity'])){
					$dbq->updateSaleOrder($data);
				}
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $dbq->getSaleorderItemById($id);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/sales/index");
		}if($row['is_approved']==1){
			$user = $this->GetuserInfoAction();
			if($user['level']!=1){
				Application_Form_FrmMessage::Sucessfull("SALE_ORDER_WARNING","/sales/index");
			}
		}
		$this->view->rs = $dbq->getSaleorderItemDetailid($id);
		$this->view->rsterm = $dbq->getTermconditionByid($id);
		
		///link left not yet get from DbpurchaseOrder
		$frm_purchase = new Sales_Form_FrmSale(null);
		$form_sale = $frm_purchase->SaleOrder($row);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		$this->view->discount_type = $row['discount_type'];
		 
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$this->view->term_opt = $db->getAllTermCondition(1);
		
		
	}	
	
	function requestlistAction(){
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
		$db = new Sales_Model_DbTable_DbRequest();
		$rows = $db->getAllRequestOrder($search);
		$this->view->rs = $rows;
		$columns=array("BRANCH_NAME","SALE_NO","REQUEST_NAME","POSITION","PLAN","ORDER_DATE","TOTAL_AMOUNT","BY_USER","STATUS");
		$link=array(
				'module'=>'sales','controller'=>'index','action'=>'editrequest',
		);
		$link1=array(
				'module'=>'sales','controller'=>'index','action'=>'viewapp',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('location'=>$link,'request_name'=>$link,'position'=>$link,
				'sale_no'=>$link,'plan'=>$link));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	function addrequestAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			
			try {
				$data = $this->getRequest()->getPost();
				//print_r($data);exit();
				$dbq = new Sales_Model_DbTable_DbRequest();
				if(!empty($data['identity'])){
					$id = $dbq->addRequestOrder($data);
				}
				//Application_Form_FrmMessage::message("INSERT_SUCESS");
				if(isset($data['save_close'])){
					//Application_Form_FrmMessage::redirectUrl("/sales/index/requestlist");
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/sales/index/requestlist');
				}elseif(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/sales/index/viewrequest/id/".$id);
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		///link left not yet get from DbpurchaseOrder
		$frm_purchase = new Sales_Form_FrmRequest(null);
		$form_sale = $frm_purchase->SaleOrder(null);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		 
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$this->view->worktype = $items->getWorkType();
		$this->view->term_opt = $db->getAllTermCondition(1);
		
		$this->view->product = $items->getAllProduct();
		
		
		$formpopup = new Sales_Form_FrmCustomer(null);
		$formpopup = $formpopup->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
	}
	
	function editrequestAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Sales_Model_DbTable_DbRequest();
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"]=$id;
			try {
				if(!empty($data['identity'])){
					$dbq->updateRequestOrder($data);
				}
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/index/requestlist");
				}elseif(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/sales/index/viewrequest/id/".$id);
				}else{
					Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/index/addrequest");
				}
				//Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/index/requestlist");
			}catch (Exception $e){
				//Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				echo $err;exit();
			}
		}
		$row = $dbq->getSaleorderItemById($id);
		$this->view->rs = $dbq->getSaleorderItemDetailid($id);
		$this->view->rsterm = $dbq->getTermconditionByid($id);
		
		///link left not yet get from DbpurchaseOrder
		$frm_purchase = new Sales_Form_FrmRequest(null);
		$form_sale = $frm_purchase->SaleOrder($row);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		$this->view->work_id = $row["work_plan"];
		
		 
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$this->view->product = $items->getAllProduct();
		$this->view->worktype = $items->getWorkType();
		$this->view->term_opt = $db->getAllTermCondition(1);
	}
	function checkrequestAction(){
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
		$db = new Sales_Model_DbTable_DbRequest();
		$rows = $db->getAllRequestOrder($search);
		$this->view->rs = $rows;
		$columns=array("BRANCH_NAME","SALE_NO","REQUEST_NAME","POSITION","PLAN","ORDER_DATE","TOTAL_AMOUNT","BY_USER","STATUS");
		$link=array(
				'module'=>'sales','controller'=>'index','action'=>'editrequest',
		);
		$link1=array(
				'module'=>'sales','controller'=>'index','action'=>'viewapp',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('location'=>$link,'request_name'=>$link,'position'=>$link,
				'sale_no'=>$link,'plan'=>$link));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	function addcheckAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/index/requestlist");
		}
		$query = new Sales_Model_DbTable_DbRequest();
		$this->view->product =  $query->getRequestById($id);
		$rs = $query->getRequestById($id);
		if(empty($rs)){
			$this->_redirect("/sales/index/requestlist");
		}
		$db= new Application_Model_DbTable_DbGlobal();
		$this->view->rscondition = $db->getTermConditionById(1, $id);
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$query->checkRequest($data);
			if(isset($data["save_print"])){
				Application_Form_FrmMessage::Sucessfull("CHECK_SUCESS","/sales/index/viewrequest/id/".$id);
			}else{
				Application_Form_FrmMessage::Sucessfull("CHECK_SUCESS","/sales/index/checkrequest");
			}
		}
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	function requestdeliveryAction(){
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
		$db = new Sales_Model_DbTable_DbRequest();
		$rows = $db->getAllRequestOrder($search);
		$this->view->rs = $rows;
		$columns=array("BRANCH_NAME","SALE_NO","REQUEST_NAME","POSITION","PLAN","ORDER_DATE","TOTAL_AMOUNT","BY_USER","STATUS");
		$link=array(
				'module'=>'sales','controller'=>'index','action'=>'editrequest',
		);
		$link1=array(
				'module'=>'sales','controller'=>'index','action'=>'viewapp',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('location'=>$link,'request_name'=>$link,'position'=>$link,
				'sale_no'=>$link,'plan'=>$link));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
		
	}
	function addrequestdeliveryAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/index/requestlist");
		}
		$query = new Sales_Model_DbTable_DbRequest();
		$this->view->product =  $query->getRequestById($id);
		$rs = $query->getRequestById($id);
		if(empty($rs)){
			$this->_redirect("/sales/index/requestlist");
		}
		$db= new Application_Model_DbTable_DbGlobal();
		$this->view->rscondition = $db->getTermConditionById(1, $id);
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$query->addDelivery($data);
			if(isset($data["save_print"])){
				Application_Form_FrmMessage::redirectUrl("DELIVER_SUCESS","/sales/index/viewrequest/id/".$id);
			}else{
				Application_Form_FrmMessage::Sucessfull("DELIVER_SUCESS","/sales/index/checkrequest");
			}
		}
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	function viewrequestAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/index/requestlist");
		}
		$query = new Sales_Model_DbTable_DbRequest();
		$this->view->product =  $query->getRequestById($id);
		$rs = $query->getRequestById($id);
		if(empty($rs)){
			$this->_redirect("/sales/index/requestlist");
		}
		$db= new Application_Model_DbTable_DbGlobal();
		$this->view->rscondition = $db->getTermConditionById(1, $id);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	function viewappAction(){
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Sales_Model_DbTable_DbSaleOrder();
				$dbq->RejectSale($data);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/salesapprove");
		}
		$query = new Sales_Model_DbTable_Dbsalesapprov();
		$rs = $query->getProductSaleById($id);
		if(empty($rs)){
			$this->_redirect("/sales/salesapprove");
		}
		$this->view->product = $rs;
	}
	public function getproductpriceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db ->getProductPriceBytype($post['customer_id'], $post['product_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	function getsonumberAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$qo = $db->getSalesNumber($post['branch_id']);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
		
}