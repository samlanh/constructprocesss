<?php
class Purchase_siterequestController extends Zend_Controller_Action
{	
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		// $db = new Application_Model_DbTable_DbGlobal();
		// $rs = $db->getValidUserUrl();
		// if(empty($rs)){
			// Application_Form_FrmMessage::Sucessfull("YOU_NO_PERMISION_TO_ACCESS_THIS_SECTION","/index/dashboad");
		// }
    }
	function indexAction(){
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
	function addAction(){
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
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/purchase/siterequest/index');
				}elseif(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/purchase/siterequest/viewrequest/id/".$id);
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
	
	function editAction(){
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
					Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/purchase/siterequest/index");
				}elseif(isset($data["save_print"])){
					Application_Form_FrmMessage::redirectUrl("/purchase/siterequest/viewrequest/id/".$id);
				}else{
					Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/purchase/siterequest/add");
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
		$this->view->row = $row;
		$this->view->rs = $dbq->getSaleorderItemDetailid($id);
		print_r($dbq->getSaleorderItemDetailid($id));
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
		
		$formpopup = new Sales_Form_FrmCustomer(null);
		$formpopup = $formpopup->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
	}
	function checklistAction(){
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
			$this->_redirect("/purchase/siterequest/requestlist");
		}
		$query = new Sales_Model_DbTable_DbRequest();
		$this->view->product =  $query->getRequestById($id);
		$rs = $query->getRequestById($id);
		if(empty($rs)){
			$this->_redirect("/purchase/siterequest/index");
		}
		$db= new Application_Model_DbTable_DbGlobal();
		$this->view->rscondition = $db->getTermConditionById(1, $id);
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$query->checkRequest($data);
			if(isset($data["save_print"])){
				Application_Form_FrmMessage::Sucessfull("CHECK_SUCESS","/purchase/siterequest/viewrequest/id/".$id);
			}else{
				Application_Form_FrmMessage::Sucessfull("CHECK_SUCESS","/purchase/siterequest/checklist");
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
				'module'=>'sales','controller'=>'index','action'=>'edit',
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
			$this->_redirect("/purchase/siterequest/index");
		}
		$query = new Sales_Model_DbTable_DbRequest();
		$this->view->product =  $query->getRequestById($id);
		$rs = $query->getRequestById($id);
		if(empty($rs)){
			$this->_redirect("/purchase/siterequest/index");
		}
		$db= new Application_Model_DbTable_DbGlobal();
		$this->view->rscondition = $db->getTermConditionById(1, $id);
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$query->addDelivery($data);
			if(isset($data["save_print"])){
				Application_Form_FrmMessage::redirectUrl("DELIVER_SUCESS","/purchase/siterequest/viewrequest/id/".$id);
			}else{
				Application_Form_FrmMessage::Sucessfull("DELIVER_SUCESS","/purchase/siterequest/checklist");
			}
		}
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
	function viewrequestAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/purchase/siterequest/index");
		}
		$query = new Sales_Model_DbTable_DbRequest();
		$this->view->product =  $query->getRequestById($id);
		$rs = $query->getRequestById($id);
		if(empty($rs)){
			$this->_redirect("/purchase/siterequest/index");
		}
		$db= new Application_Model_DbTable_DbGlobal();
		$this->view->rscondition = $db->getTermConditionById(1, $id);
		
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->title_reprot = $db->getTitleReport($session_user->location_id);
	}
}