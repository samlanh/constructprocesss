<?php 
class Sales_Form_FrmPlan extends Zend_Form
{
	protected $tr;
	public function init()
    {
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	///From plan 
	function frmPlanType($data=null){
		$db = new Sales_Model_DbTable_DbPlan();
		$row = $db->getStatus();
		$name_type = new Zend_Form_Element_Text("nametype");
		$name_type->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		$status = new Zend_Form_Element_Select("status");
		$opt = array();
		if(!empty($row)){
			foreach($row as $rs){
				$opt[$rs["key_code"]] = $rs["name_en"];
			}
		}
		$status->setAttribs(array(
				'class'=>'form-control select2me',
				'required'=>'required',
		));
		$status->setMultiOptions($opt);
		
		if($data!=null){
			$name_type->setValue($data["name"]);
			$status->setValue($data["status"]);
		}
		$this->addElements(array($name_type,$status));
		return $this;
	}
	
	function frmWorkPlan($data=null){
		$db = new Sales_Model_DbTable_DbPlan();
		$rs_plan = $db->getAllPlan();
		$row = $db->getStatus();
		$name = new Zend_Form_Element_Text("name");
		$name->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		$status = new Zend_Form_Element_Select("status");
		$opt = array();
		if(!empty($row)){
			foreach($row as $rs){
				$opt[$rs["key_code"]] = $rs["name_en"];
			}
		}
		$status->setAttribs(array(
				'class'=>'form-control select2me',
				'required'=>'required',
		));
		$status->setMultiOptions($opt);
		
		$plan = new Zend_Form_Element_Select("plan");
		$opt = array(''=>'Select Plan');
		if(!empty($rs_plan)){
			foreach($rs_plan as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$plan->setAttribs(array(
				'class'=>'form-control select2me',
				'required'=>'required',
		));
		$plan->setMultiOptions($opt);
		
		
		
		if($data!=null){
			$name->setValue($data["name"]);
			$status->setValue($data["status"]);
			$plan->setValue($data["plan_id"]);
		}
		$this->addElements(array($name,$status,$plan));
		return $this;
	}
	/////////////	Form Sales		/////////////////
	public function add($data=null){
		$db = new Sales_Model_DbTable_DbPlan();
		$row_status = $db->getStatus();
		$row_type = $db->getType();
		//$row_cate = $db->getPlanType();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$p_code = $db->getPlanCode();
		$code = new Zend_Form_Element_Text('code');
		$code ->setAttribs(array(
    			'class' => 'form-control',
    	));
		$code->setValue($p_code);
		$db_globle=new Application_Model_DbTable_DbGlobal();
		$customerid=new Zend_Form_Element_Select('customer_id');
    	$customerid ->setAttribs(array(
    			'class' => 'form-control select2me',
    			));
    	$options = $db_globle->getAllCustomer(1);
    	$customerid->setMultiOptions($options);
		
		$name = new Zend_Form_Element_Text("name");
		$name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt_u = array(''=>"select type");
		if(!empty($row_type)){
			foreach ($row_type  as $rs){
				$opt_u[$rs["id"]] = $rs["name"];
			}
		}
		$_type = new Zend_Form_Element_Select("type");
		$_type->setMultiOptions($opt_u);
		$_type->setAttribs(array(
			'class'=>'form-control select2me',
		));
		$opt_u = array(''=>"select typecategory");
		if(!empty($row_cate)){
			foreach ($row_cate  as $rs){
				$opt_u[$rs["id"]] = $rs["type"];
			}
		}
		$_typecate = new Zend_Form_Element_Select("typecate");
		$_typecate->setMultiOptions($opt_u);
		$_typecate->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$_typecate->setMultiOptions($opt_u);
		
		$opt = array(''=>$tr->translate("SELECT_CATEGORY"),-1=>$tr->translate("ADD_NEW_CATEGORY"));
		$category = new Zend_Form_Element_Select("category");
		$category->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'getPopupCategory();getSalesPrefix();',
				//'required'=>'required'
		));
		$address = new Zend_Form_Element_Textarea("address");
		$address->setAttribs(array(
				'class'=>'form-control',
				'style'=>'height:50px'
		));
		$type_name = new Zend_Form_Element_Text("typename");
		$type_name->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		$status = new Zend_Form_Element_Select("status");
		$opt = array();
		if(!empty($row_status)){
			foreach($row_status as $rs){
				$opt[$rs["key_code"]] = $rs["name_en"];
			}
		}
		$status->setAttribs(array(
				'class'=>'form-control select2me',
				//'Onchange'	=>	'getMeasureLabel()'
		));
		$status->setMultiOptions($opt);
		$status->setValue($request->getParam("status"));
		$opt=array();
    	$rows = $db_globle->getGlobalDb('SELECT id ,name FROM `tb_sale_agent` WHERE name!="" AND status=1');
    	if(!empty($rows)) {
    		foreach($rows as $rs) $opt[$rs['id']]=$rs['name'];
    	}
    	$saleagent_id = new Zend_Form_Element_Select('saleagent_id');
    	$saleagent_id->setAttribs(array('class'=>'demo-code-language form-control select2me'));
    	$saleagent_id->setMultiOptions($opt);
		
		$desc = new Zend_Form_Element_Textarea("desc");
		$desc->setAttribs(array('class'=>'form-control','id'=>'editor1','rows'=>'10','cols'=>'80'));
		
		$date_line_plan = new Zend_Form_Element_Text("date_line_plan");
		$date_line_plan->setAttribs(array('class'=>'form-control'));
		
		$date_line_qo = new Zend_Form_Element_Text("date_line_qo");
		$date_line_qo->setAttribs(array('class'=>'form-control date-picker'));
		$date_line_qo->setValue(date("m/d/Y"));
		
		$plan_goald =  new Zend_Form_Element_Text("plan_goald");
		$plan_goald->setAttribs(array('class'=>'form-control'));
		
		$remark =  new Zend_Form_Element_Text("remark");
		$remark->setAttribs(array('class'=>'form-control'));
		
		$file =  new Zend_Form_Element_File("file[]");
		$file->setAttribs(array('class'=>'form-control'));
		
		$reject_note = new Zend_Form_Element_Textarea("reject_note");
		$reject_note->setAttribs(array('class'=>'form-control',"style"=>"height:50px;","readOnly"=>"readOnly"));
		$this->addElement($reject_note);
		
		if($data!=null){
			$old_file = new Zend_Form_Element_Hidden("old_file");
			$this->addElement($old_file);
			$code->setValue($data["code"]);
			$address->setValue($data["address"]);
			$_type->setValue($data["type"]);
			$name->setValue($data["name"]);
			$status->setValue($data["status"]);
			$customerid->setValue($data["customer_id"]);
			$saleagent_id->setValue($data["sale_id"]);
			$desc->setValue($data["description"]);
			$plan_goald->setValue($data["plan_goald"]);
			$date_line_plan->setValue($data["date_line_plan"]);
			$date_line_qo->setValue(date("m/d/Y",strtotime($data["date_line_qo"])));
			$remark->setValue($data["remark"]);
			$old_file->setValue($data["file"]);
			$reject_note->setValue($data["reject_note"]);
		}
		
		
		
		$this->addElements(array($file,$remark,$date_line_plan,$date_line_qo,$plan_goald,$code,$desc,$saleagent_id,$customerid,$status,$_typecate,$type_name,$_type,$address,$name));
		return $this;
	}
	function SalesFilter(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db_p = new Sales_Model_DbTable_DbPlan();
		$row_cate = $db_p->getType();
		$row = $db_p->getStatus();
		$ad_search = new Zend_Form_Element_Text("adv_search");
		$ad_search->setAttribs(array(
				'class'=>'form-control',
		));
		$ad_search->setValue($request->getParam("adv_search"));
		$opt_u = array(''=>"select type");
		if(!empty($row_cate)){
			foreach ($row_cate  as $rs){
				$opt_u[$rs["id"]] = $rs["name"];
			}
		}
		$_typecate = new Zend_Form_Element_Select("typecate");
		$_typecate->setMultiOptions($opt_u);
		$_typecate->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$_typecate->setMultiOptions($opt_u);
		$status = new Zend_Form_Element_Select("status");
		$opt = array('1'=>$tr->translate("ACTIVE"),'2'=>$tr->translate("DEACTIVE"));
		$status->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$status->setMultiOptions($opt);
		$status->setValue($request->getParam("status"));
		
		$db_globle=new Application_Model_DbTable_DbGlobal();
		$customerid=new Zend_Form_Element_Select('customer_id');
    	$customerid ->setAttribs(array(
    			'class' => 'form-control select2me',
    			));
    	$options = $db_globle->getAllCustomer(1);
    	$customerid->setMultiOptions($options);
		$this->addElements(array($_typecate,$ad_search,$status,$customerid));
		return $this;
	}
	
}
	