<?php

class Sales_Form_FrmRequest extends Zend_Form
{
    protected function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function SaleOrder($data=null)
    {
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
		$id = $result["branch_id"];
    	$db_r = new Sales_Model_DbTable_DbRequest();
		
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
		$staff = $db->getAllStaff();
		
		$date =new Zend_Date();
		
		$code = $db_r->getRequestCode($id);

    	/*$request_by=new Zend_Form_Element_Text('request_by');
    	$request_by ->setAttribs(array(
    			'class' => 'form-control',
    			'Onchange'=>'getCustomerInfo()','required'=>"required",
    			));
    	$this->addElement($request_by);*/
		
		$opt_request_type = array(1=>$tr->translate("REQUEST_FOR_SITE"),2=>$tr->translate("REQUEST_FOR_BOQ"));
		$request_type=new Zend_Form_Element_Select('request_type');
    	$request_type ->setAttribs(array(
    			'class' => 'form-control select2me',
    			'Onchange'=>'getItem();','required'=>"required",
    			));
		
		$request_type->setMultiOptions($opt_request_type);
    	$this->addElement($request_type);
		
		$opt_satff = array(''=>$tr->translate("SELECT"));
		$request_by=new Zend_Form_Element_Select('request_by');
    	$request_by ->setAttribs(array(
    			'class' => 'form-control select2me',
    			'Onchange'=>'getStaff()','required'=>"required",
    			));
		if(!empty($staff)){
			foreach($staff as $rs){
				$opt_satff[$rs["id"]] = $rs["name"];
			}
		}
		$request_by->setMultiOptions($opt_satff);
    	$this->addElement($request_by);
		
		$requstman_pos=new Zend_Form_Element_Text('requstman_pos');
    	$requstman_pos ->setAttribs(array(
    			'class' => 'form-control',
    			'Onchange'=>'getCustomerInfo()','required'=>"required",
    			));
    	$this->addElement($requstman_pos);
		
		$requst_no=new Zend_Form_Element_Text('request_no');
    	$requst_no ->setAttribs(array(
    			'class' => 'form-control',
    			'Onchange'=>'getCustomerInfo()','required'=>"required",
    			));
    	$this->addElement($requst_no);
		
		$requst_date=new Zend_Form_Element_Text('request_date');
    	$requst_date ->setAttribs(array(
    			'class' => 'form-control date-picker',
    			'Onchange'=>'getCustomerInfo()','required'=>"required",
    			));
		$requst_date->setValue($date->get('MM/d/Y'));
    	$this->addElement($requst_date);
		
		
		$row = $db_r->getPlan();
		$option = array(''=>$tr->translate("SELECT_PLAN"));
		if(!empty($row)){
			foreach($row as $rs){
				$option[$rs["id"]] = $rs["name"];
			}
		}
		$plan=new Zend_Form_Element_Select('plan');
    	$plan ->setAttribs(array(
    			'class' => 'form-control select2me',
				'onChange'=>'getWork();getItem();','required'=>"required",
//'Onchange'=>'getCustomerInfo()',
    			));
    	$plan->setMultiOptions($option);
    	$this->addElement($plan);
		
		$row_work = $db_r->getWorkPlan();
		$option = array(''=>$tr->translate("SELECT_WORK_PLAN"));
		/*if(!empty($row_work)){
			foreach($row_work as $rs){
				$option[$rs["id"]] = $rs["name"];
			}
		}*/
		$work_plan=new Zend_Form_Element_Select('work_plan');
    	$work_plan ->setAttribs(array(
    			'class' => 'form-control select2me',
				'onChange'=>'','required'=>"required",
//'Onchange'=>'getCustomerInfo()',
    			));
    	$work_plan->setMultiOptions($option);
    	$this->addElement($work_plan);
		
		$plan_addr=new Zend_Form_Element_Text('plan_addr');
    	$plan_addr ->setAttribs(array(
    			'class' => 'form-control',
    			'Onchange'=>'getCustomerInfo()',
    			));
    	$this->addElement($plan_addr);
		
    	$roder_element= new Zend_Form_Element_Text("apno");
    	$roder_element->setAttribs(array('placeholder' => 'Optional','class'=>'form-control',"readonly"=>true,
    			"onblur"=>"CheckPOInvoice();"));
    	$this->addElement($roder_element);
    	$qo = $db->getRequestNumber($id);
    	$roder_element->setValue($qo);
    	$this->addElement($roder_element);
    	
    	$user= $this->GetuserInfo();
    	$options="";
		
    	$locationID = new Zend_Form_Element_Select('branch_id');
    	$locationID ->setAttribs(array('class'=>'form-control select2me'));
		$options = $db->getAllLocation(1);
    	$locationID->setMultiOptions($options);
    	$locationID->setattribs(array(
    			'Onchange'=>'getQuotenumber()',));
				
		$locationID->setValue($id);
    	$this->addElement($locationID);
    	    	
    	$rowspayment= $db->getGlobalDb('SELECT * FROM tb_paymentmethod');
    	if($rowspayment) {
    		foreach($rowspayment as $readCategory) $options_cg[$readCategory['payment_typeId']]=$readCategory['payment_name'];
    	}
    	$paymentmethodElement = new Zend_Form_Element_Select('payment_name');
    	$paymentmethodElement->setMultiOptions($options_cg);
    	$this->addElement($paymentmethodElement);
    	$paymentmethodElement->setAttribs(array("class"=>"form-control select2me"));
    	$rowsPayment = $db->getGlobalDb('SELECT id, description,symbal FROM tb_currency WHERE status = 1 ');
    	if($rowsPayment) {
    		foreach($rowsPayment as $readPayment) $options_cur[$readPayment['id']]=$readPayment['description'].$readPayment['symbal'];
    	}	 
    	// $currencyElement = new Zend_Form_Element_Select('currency');
    	// $currencyElement->setAttribs(array('class'=>'demo-code-language form-control select2me'));
    	// $currencyElement->setMultiOptions($options_cur);
    	// $this->addElement($currencyElement);
    	
    	$descriptionElement = new Zend_Form_Element_Textarea('remark');
    	$descriptionElement->setAttribs(array("class"=>'form-control',"rows"=>3));
    	$this->addElement($descriptionElement);
    	
    	$allTotalElement = new Zend_Form_Element_Hidden('all_total');
    	$allTotalElement->setAttribs(array("class"=>"form-control",'readonly'=>'readonly','style'=>'text-align:right'));
    	$this->addElement($allTotalElement);
    	
    	$netTotalElement = new Zend_Form_Element_Text('net_total');
    	$netTotalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($netTotalElement);
    	
    	$opt=array();
    	$rows = $db->getGlobalDb('SELECT id ,name FROM `tb_sale_agent` WHERE name!="" AND status=1');
    	if(!empty($rows)) {
    		foreach($rows as $rs) $opt[$rs['id']]=$rs['name'];
    	}
    	$saleagent_id = new Zend_Form_Element_Select('saleagent_id');
    	$saleagent_id->setAttribs(array('class'=>'demo-code-language form-control select2me'));
    	$saleagent_id->setMultiOptions($opt);
    	$this->addElement($saleagent_id);
    	
    	
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px form-control','onblur'=>'doTotal()',));
    	$this->addElement($discountValueElement);
    	
    	$discountRealElement = new Zend_Form_Element_Text('discount_real');
    	$discountRealElement->setAttribs(array('readonly'=>'readonly','class'=>'input100px form-control',));
    	$this->addElement($discountRealElement);
    	
    	$globalRealElement = new Zend_Form_Element_Hidden('global_disc');
    	$globalRealElement->setAttribs(array("class"=>"form-control"));
    	$this->addElement($globalRealElement);
    	
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px','onblur'=>'doTotal();','style'=>'text-align:right'));
    	$this->addElement($discountValueElement);
    	
    	$dis_valueElement = new Zend_Form_Element_Hidden('dis_value');
    	$dis_valueElement->setAttribs(array("required"=>1,'placeholder' => 'Discount Value','style'=>'text-align:right'));
    	$dis_valueElement->setValue(0);
    	$dis_valueElement->setAttribs(array("onkeyup"=>"calculateDiscount();","class"=>"form-control"));
    	$this->addElement($dis_valueElement);
    	
    	$totalAmountElement = new Zend_Form_Element_Text('totalAmoun');
    	$totalAmountElement->setAttribs(array('readonly'=>'readonly','style'=>'text-align:right',"class"=>"form-control"
    	));
    	$this->addElement($totalAmountElement);
    	
    	$remainlElement = new Zend_Form_Element_Text('remain');
    	$remainlElement->setAttribs(array('readonly'=>'readonly','style'=>'text-align:right',"class"=>"red form-control"));
    	$this->addElement($remainlElement);
    	
    	$balancelElement = new Zend_Form_Element_Text('balance');
    	$balancelElement->setAttribs(array('readonly'=>'readonly','style'=>'text-align:right',"class"=>"form-control"));
    	$this->addElement($balancelElement);
    	
    	$date_inElement = new Zend_Form_Element_Text('date_in');
    	
    	$date_inElement ->setAttribs(array('class'=>'form-control form-control-inline date-picker'));
    	$date_inElement ->setValue($date->get('MM/d/Y'));
    	$this->addElement($date_inElement);
    	
    	$dateOrderElement = new Zend_Form_Element_Text('order_date');
    	$dateOrderElement ->setAttribs(array('class'=>'col-md-3 form-control form-control-inline date-picker','placeholder' => 'Click to Choose Date'));
    	$dateOrderElement ->setValue($date->get('MM/d/Y'));
    	$this->addElement($dateOrderElement);
    	
    	$dateElement = new Zend_Form_Element_Text('date');
    	$this->addElement($dateElement);
    	 
    	$totalElement = new Zend_Form_Element_Text('total');
    	$this->addElement($totalElement);
    	
    	$totaTaxElement = new Zend_Form_Element_Text('total_tax');
    	$totaTaxElement->setAttribs(array('class'=>'custom[number] form-control','style'=>'text-align:right'));
    	$this->addElement($totaTaxElement);
    	
    	$paidElement = new Zend_Form_Element_Text('paid');
    	$paidElement->setAttribs(array('class'=>'custom[number] form-control','onkeyup'=>'doRemain();','style'=>'text-align:right'));
    	$this->addElement($paidElement);
		
		// $opt_re = array(''=>$tr->translate("SELECT"),'1'=>'E','2'=>"W",3=>"G",4=>"M",5=>"P",6=>"S",7=>"MT",8=>"K",9=>"OT");
		// $request_type = new Zend_Form_Element_Select("re_type");
		// $request_type->setAttribs(array('class'=>'demo-code-language form-control select2me'));
    	// $request_type->setMultiOptions($opt_re);
    	// $this->addElement($request_type);
		
		$reject = new Zend_Form_Element_Textarea("reject");
		$reject->setAttribs(array('class'=>'form-control','style'=>'height: 40px;'));
		$this->addElement($reject);
		
		$boq_id = new Zend_Form_Element_Hidden("boq_id");
		$boq_id->setAttribs(array('class'=>'form-control','onChange'=>"getItemBOQ();"));
		$this->addElement($boq_id);
    	
    	Application_Form_DateTimePicker::addDateField(array('order_date','date_in'));
    		if($data != null) {
    			$idElement = new Zend_Form_Element_Hidden('id');
    			$this->addElement($idElement);
    			$idElement ->setValue($data["id"]);
				
				$old_location = new Zend_Form_Element_Hidden('old_location');
    			$this->addElement($old_location);
    			$old_location ->setValue($data["branch_id"]);
				
    			$request_by->setValue($data["user_request_id"]);
    			$locationID->setValue($data['branch_id']);
    			$requstman_pos->setValue($data['position']);
    			$saleagent_id->setValue($data['saleagent_id']);
    			$descriptionElement->setValue($data['remark']);
    			$dateOrderElement->setValue(date("m/d/Y",strtotime($data['date_sold'])));
    			$roder_element->setValue($data['sale_no']);
    			$totalAmountElement->setValue($data['all_total']);
    			$dis_valueElement->setValue($data['discount_value']);
    			$allTotalElement->setValue($data['net_total']);
				$plan->setValue($data["plan_id"]);
				$request_type->setValue($data["type"]);
				//$work_plan->setValue($data["work_plan"]);
				$reject->setValue($data["approved_note"]);
				$requst_no->setValue($data["number_work_request"]);
				$requst_date->setValue(date("m/d/Y",strtotime($data["date_work_request"])));
				
    		} else {
    	}
     	return $this;
    }

}

