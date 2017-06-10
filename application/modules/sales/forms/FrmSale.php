<?php

class Sales_Form_FrmSale extends Zend_Form
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
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();

    	$customerid=new Zend_Form_Element_Select('customer_id');
    	$customerid ->setAttribs(array(
    			'class' => 'form-control select2me',
    			'Onchange'=>'getCustomerInfo()',
    			));
    	$options = $db->getAllCustomer(1);
    	$customerid->setMultiOptions($options);
    	
    	$roder_element= new Zend_Form_Element_Text("txt_order");
    	$roder_element->setAttribs(array('placeholder' => 'Optional','class'=>'form-control',
    			"onblur"=>"CheckPOInvoice();"));
    	$qo = $db->getSalesNumber(1);
    	$roder_element->setValue($qo);
    	
    	$user= $this->GetuserInfo();
    	$options="";
//     	$locationID = new Zend_Form_Element_Select('branch_id');
//     	$locationID ->setAttribs(array('class'=>'form-control select2me'));
// 		$options = $db->getAllLocation(1);
//     	$locationID->setMultiOptions($options);
//     	$locationID->setattribs(array(
//     			'Onchange'=>'getsaleOrderNumber()',));
//     	$this->addElement($locationID);
    	    	
    	$descriptionElement = new Zend_Form_Element_Textarea('remark');
    	$descriptionElement->setAttribs(array("class"=>'form-control',"rows"=>3));
    	$this->addElement($descriptionElement);
    	
    	$allTotalElement = new Zend_Form_Element_Text('all_total');
    	$allTotalElement->setAttribs(array("class"=>"form-control",'readonly'=>'readonly','style'=>'text-align:right'));
    	
    	$netTotalElement = new Zend_Form_Element_Text('net_total');
    	$netTotalElement->setAttribs(array('readonly'=>'readonly',));
    	
    	$opt=array();
    	$rows = $db->getGlobalDb('SELECT id ,name FROM `tb_sale_agent` WHERE name!="" AND status=1');
    	if(!empty($rows)) {
    		foreach($rows as $rs) $opt[$rs['id']]=$rs['name'];
    	}
    	$saleagent_id = new Zend_Form_Element_Select('saleagent_id');
    	$saleagent_id->setAttribs(array('class'=>'demo-code-language form-control select2me'));
    	$saleagent_id->setMultiOptions($opt);
    	
    	$project_type = new Zend_Form_Element_Select('project_type');
    	$project_type->setAttribs(array('class'=>'demo-code-language form-control select2me'));
    	$project_type->setMultiOptions(array(1=>"Only Labour",2=>"Labour and Material"));
    	
    	
    	$project_name = new Zend_Form_Element_Select('project_name');
    	$project_name->setAttribs(array('class'=>'form-control select2me'));
    	$optplan = $db->getAllPlan(1);
    	$project_name->setMultiOptions($optplan);
    	
    	$duration = new Zend_Form_Element_Text('duration');
    	$duration->setAttribs(array('class'=>'form-control'));
    	
    	$warranty = new Zend_Form_Element_Text('warranty');
    	$warranty->setAttribs(array('class'=>'form-control'));
    	
    	$start_date = new Zend_Form_Element_Text('start_date');
    	$start_date ->setAttribs(array('class'=>'col-md-3 validate[required] form-control form-control-inline date-picker',
    			'onchange'=>'calculateDuration();'));
    	$start_date ->setValue(date('d/m/Y'));
    	
    	$end_date = new Zend_Form_Element_Text('end_date');
    	$end_date ->setAttribs(array('class'=>'col-md-3 validate[required] form-control form-control-inline date-picker',
    			'onchange'=>'calculateDuration();'));
    	$end_date ->setValue(date('d/m/Y'));
    	
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px','onblur'=>'doTotal();','style'=>'text-align:right'));
    	
    	$dis_valueElement = new Zend_Form_Element_Text('dis_value');
    	$dis_valueElement->setAttribs(array("required"=>1,'placeholder' => 'Discount Value','style'=>'text-align:right'));
    	$dis_valueElement->setValue(0);
    	$dis_valueElement->setAttribs(array("onkeyup"=>"calculateDiscount();","class"=>"form-control"));
    	
    	$totalAmountElement = new Zend_Form_Element_Text('net_total');
    	$totalAmountElement->setAttribs(array('readonly'=>'readonly','style'=>'text-align:right',"class"=>"form-control"));
    	
    	$dateOrderElement = new Zend_Form_Element_Text('boq_date');
    	$dateOrderElement ->setAttribs(array('class'=>'col-md-3 validate[required] form-control form-control-inline date-picker','placeholder' => 'Click to Choose Date'));
    	$dateOrderElement ->setValue(date('m/d/Y'));
    	
    	$descriptionElement = new Zend_Form_Element_Textarea('remark');
    	$descriptionElement->setAttribs(array("class"=>'form-control',"rows"=>3));
    	$this->addElement($descriptionElement);
    	
    	$labor_remark = new Zend_Form_Element_Textarea('labor_remark');
    	$labor_remark->setAttribs(array("class"=>'form-control',"rows"=>3));
    	$this->addElement($labor_remark);
    	
    	$project_address = new Zend_Form_Element_Textarea('project_address');
    	$project_address->setAttribs(array("class"=>'form-control',"rows"=>3));
    	$this->addElement($project_address);
    	
    	$totaTaxElement = new Zend_Form_Element_Text('total_tax');
    	$totaTaxElement->setAttribs(array('class'=>'custom[number] form-control','style'=>'text-align:right'));
    		if($data != null){
    			$idElement = new Zend_Form_Element_Hidden('id');
    			$this->addElement($idElement);
    			$idElement ->setValue($data["id"]);
    			$customerid->setValue($data["customer_id"]);
//     			$locationID->setValue($data['branch_id']);
    			$saleagent_id->setValue($data['saleagent_id']);
    			$descriptionElement->setValue($data['remark']);
    			$dateOrderElement->setValue(date("m/d/Y",strtotime($data['date_sold'])));
    			$roder_element->setValue($data['sale_no']);
    			$totalAmountElement->setValue($data['all_total']);
    			if($data['discount_type']==1){$data['discount_value']=$data['discount_value']."%";}
    			$dis_valueElement->setValue($data['discount_value']);
    			$allTotalElement->setValue($data['net_total']);
    		}
		$this->addElements(array($project_type,$warranty,$duration,$start_date,$end_date,$project_name,$customerid,$roder_element,$roder_element,
		$allTotalElement,$netTotalElement,$saleagent_id,$discountValueElement,
		$discountValueElement,$dis_valueElement,
		$totalAmountElement,$dateOrderElement,$totaTaxElement));
     	return $this;
    }

}

