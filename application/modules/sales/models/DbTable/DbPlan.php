<?php
class Sales_Model_DbTable_DbPlan extends Zend_Db_Table_Abstract{
    protected $_name = 'tb_plan';
    public function setName($name){
    	$this->_name=$name;
		}
		
	public function getPlanCode(){
		$db= $this->getAdapter();
    	$sql = "SELECT p.id FROM `tb_plan` AS p ORDER BY p.`id` DESC LIMIT 1";
    	$rows = $db->fetchOne($sql);
    	$pre='';
    	$new_acc_no= (int)$rows+1;
    	$rows= strlen((int)$rows+1);
    	for($i = $rows;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
	function getStatus(){
		$db= $this->getAdapter();
		$sql ="SELECT v.`key_code`,v.`name_en` FROM `tb_view` AS v WHERE v.`type`=1";
		return $db->fetchAll($sql);
	}
    public function add($_data){
		//print_r($_data);exit();
		$this->_name='tb_plan';
		$part= PUBLIC_PATH.'/files/';
		//$file = $_data['filesToUpload'];
		$session_user=new Zend_Session_Namespace('auth');
		$db =$this->getAdapter();
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$count=0;
		$i=0;
		$file_name ='';
		$count = $_FILES['filesToUpload'];
		//print_r($count);exit();
			if(count($_FILES['filesToUpload']["name"])) {
				foreach ($_FILES['filesToUpload']["name"] as $key=>$file) {$i++;
					if($_FILES['filesToUpload']["name"][$key]){
						$temp = str_replace(" ","_",$file);
						$newfilename = $_data["code"]."_". $temp;
						move_uploaded_file($_FILES['filesToUpload']["tmp_name"][$key], $part . $newfilename);
						//do your upload stuff here
						if($i==1){
							$file_name = $newfilename;
						}else{
							$file_name = $file_name.",".$newfilename;
						}
					}
				}
				//echo $file_name;
			}
	//exit();
		$_arr = array(
			'type'				=>	$_data['type'],
			'date_line_plan'	=>	$_data["date_line_plan"],
			'date_line_qo'		=>	date("Y-m-d",strtotime($_data["date_line_qo"])),
			'plan_goald'		=>	date("Y-m-d",strtotime($_data["plan_goald"])),
			'file'				=>	$file_name,
			'code'				=>	$_data['code'],
			'name'				=>	$_data['name'],
			'sale_id'			=>	$_data["saleagent_id"],
			'customer_id'		=>	$_data["customer_id"],
			'description'		=>	$_data["desc"],
			'address'			=>	$_data['address'],
			'status'			=>	$_data['status'],
			'date'				=> 	date('Y-m-d'),
			'remark'			=>	$_data["remark"],
			'pedding'			=>	1,
			'appr_status'		=>	0,
			'user_id'			=>	$GetUserId,
		);
		$this->insert($_arr);
	}
	function getUser(){
		$db = $this->getAdapter();
		$sql ="SELECT u.id,u.`first_name` FROM `rms_users` AS u";
		return $db->fetchAll($sql);
	}
    public function updateplanById($_data){
		$session_user=new Zend_Session_Namespace('auth');
		$db =$this->getAdapter();
		$db->beginTransaction();
		try{
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$part= PUBLIC_PATH.'/files/';
		//$file = $_data['filesToUpload'];
		$count=0;
		$i=0;
		$file_name ='';
			if(count($_FILES['filesToUpload']["name"])) {
				foreach ($_FILES['filesToUpload']["name"] as $key=>$file) {$i++;
					if($_FILES['filesToUpload']["name"][$key]){
						$temp = str_replace(" ","_",$file);
						$newfilename = $_data["code"]."_". $temp;
						move_uploaded_file($_FILES['filesToUpload']["tmp_name"][$key], $part . $newfilename);
						//do your upload stuff here
						if($i==1){
							$file_name = $newfilename;
						}else{
							$file_name = $file_name.",".$newfilename;
						}
					}
					
				}
				//echo $file_name;
			}
			if($file_name==''){
				$file_name = $_data["old_file"];
			}
		$_arr = array(	
			'type'				=>	$_data['type'],
			'date_line_plan'	=>	$_data["date_line_plan"],
			'date_line_qo'		=>	date("Y-m-d",strtotime($_data["date_line_qo"])),
			'plan_goald'		=>	$_data["plan_goald"],
			'file'				=>	$file_name,
			'code'				=>	$_data['code'],
			'name'				=>	$_data['name'],
			'sale_id'			=>	$_data["saleagent_id"],
			'customer_id'		=>	$_data["customer_id"],
			'description'		=>	$_data["desc"],
			'address'			=>	$_data['address'],
			'status'			=>	$_data['status'],
			'date'				=> 	date('Y-m-d'),
			'remark'			=>	$_data["remark"],
			'pedding'			=>	1,
			'appr_status'		=>	0,
			'user_id'			=>	$GetUserId,
		);
		$this->_name='tb_plan';
		$where = "id =".$_data['id'];
		$this->update($_arr, $where);
		$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	
	public function check($_data){
		
		$session_user=new Zend_Session_Namespace('auth');
		$db =$this->getAdapter();
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		if($_data["check"]==1){
			$pedding = 2;
			$appr = 0;
		}else{
			$pedding = 1;
			$appr = 2;
		}
		$_arr = array(	
			'check_user'		=>	$GetUserId,
			'reason'			=>	$_data["remark"],
			'pedding'			=>	$pedding,
			'appr_status'		=>	$appr,
			
		);
		$this->_name='tb_plan';
		$where = "id =".$_data['id'];
		$this->update($_arr, $where);
		
		$rs_reject = $this->getRejectExist($_data["id"],1);
		
		$_arr_remark = array(	
			'plan_id'		=>	$_data['id'],
			'type'			=>	1,
			'remark'		=>	$_data["remark"],
			'status'		=>	1,
			'date'			=>	date("Y-m-d"),
			'user_id'		=>	$GetUserId
		);
		$this->_name='tb_plan_check_remark';
		$where = "plan_id=".$_data["id"]." AND type=1";
		if(!empty($rs_reject)){
			$this->update($_arr_remark,$where);
		}else{
			$this->insert($_arr_remark);
		}
		
		
	}
	
	function getRejectExist($id,$type){
		$db=$this->getAdapter();
		$sql="SELECT p.`plan_id`,p.type FROM `tb_plan_check_remark` AS p WHERE p.plan_id=$id AND p.type=$type";
		$row = $db->fetchRow($sql);
    	return $row;  
	}
		
	 public function addworkplan($_data){
		$this->_name='tb_work_plan';
		$_arr = array(
			'name'=>$_data['name'],
			'plan_id'=>$_data['plan'],
			'status'=>$_data['status'],
			);
		$this->insert($_arr);
	}
		
	public function editworkplan($_data){
		$this->_name='tb_work_plan';
		$_arr = array(
			'name'=>$_data['name'],
			'plan_id'=>$_data['plan'],
			'status'=>$_data['status'],
			);
		$where = "id=".$_data["id"];
		$this->update($_arr,$where);
		}
	public function getplanById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  * ,
				  (SELECT c.`cust_name` FROM `tb_customer` AS c WHERE c.id=p.`customer_id` LIMIT 1) AS customer_name,
				  (SELECT c.`contact_name` FROM `tb_customer` AS c WHERE c.id=p.`customer_id` LIMIT 1) AS contact_name,
				  (SELECT c.`phone` FROM `tb_customer` AS c WHERE c.id=p.`customer_id` LIMIT 1) AS `phone`,
				  (SELECT c.`email` FROM `tb_customer` AS c WHERE c.id=p.`customer_id` LIMIT 1) AS `email`,
				  (SELECT pt.name FROM `tb_plan_type` AS pt WHERE pt.id=p.`type` LIMIT 1) AS plan_type,
				  (SELECT s.name FROM `tb_sale_agent` AS s WHERE s.id=p.`sale_id`) AS sale_agent,
				  (SELECT s.`phone` FROM `tb_sale_agent` AS s WHERE s.id=p.`sale_id` LIMIT 1) AS sale_phone,
				  (SELECT s.`email` FROM `tb_sale_agent` AS s WHERE s.id=p.`sale_id` LIMIT 1) AS sale_email,
				  (SELECT c.remark FROM `tb_plan_check_remark` AS c WHERE c.plan_id=p.`id` LIMIT 1) AS reject_note
				FROM
				  `tb_plan` AS p 
				WHERE id =$id";
		return $db->fetchRow($sql);
		}
	public function getWorkplanById($id){
		$db = $this->getAdapter();
		$sql = " SELECT id,plan_id,name,status FROM`tb_work_plan` WHERE id = $id";
		return $db->fetchRow($sql);
	}
		
	function getAllPlan(){
		$db = $this->getAdapter();
		$sql = "SELECT p.`id`,p.`name` FROM `tb_plan` AS p WHERE p.name!='' AND p.`status`=1";
		return $db->fetchAll($sql);
	}
	public function getPlan($search){
		$db = $this->getAdapter();
		$sql = "SELECT *,
(SELECT  NAME FROM `tb_plan_type` WHERE `tb_plan_type`.`id` = `tb_plan`.`type`) AS `type`,
(SELECT c.`cust_name` FROM `tb_customer` AS c WHERE c.id=customer_id) as customer,
(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=`status` AND v.type=1 LIMIT 1) AS `status`,
(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=`pedding` AND v.type=10 LIMIT 1) AS `peddings`,
(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=`appr_status` AND v.type=9 LIMIT 1) AS `appr_status`,
(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id=user_id LIMIT 1) AS user_name
FROM `tb_plan` WHERE 1";
		$where ='';
		if(!empty($search["adv_search"])){
			$s_where=array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]= " name LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';	
		}
		if(!empty($search["status"])){
			$where.=' AND status='.$search["status"];
		}
		if(!empty($search["typecate"])){
			$where.=' AND type='.$search["typecate"];
		}
		if(!empty($search["customer_id"])){
			$where.=' AND customer_id='.$search["customer_id"];
		}
		//echo $sql.$where;
		return $db->fetchAll($sql.$where);
	}
		
	public function getWorkPlan($search){
		$db = $this->getAdapter();
		$sql = "SELECT id,name,(SELECT name FROM `tb_plan` WHERE `tb_plan`.`id` = `tb_work_plan`.`plan_id`) AS `plan` ,(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=`status` AND v.type=5 LIMIT 1) AS `status`
		FROM `tb_work_plan` WHERE 1";
		$where ='';
		if(!empty($search["adv_search"])){
			$s_where=array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]= " name LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';	
		}
		if(!empty($search["status"])){
			$where.=' AND status='.$search["status"];
		}
		if(!empty($search["typecate"])){
			$where.=' AND type='.$search["typecate"];
		}
		return $db->fetchAll($sql.$where);
	}
	function getType(){
		$db = $this->getAdapter();
		$sql ="SELECT id,name FROM `tb_plan_type`";
		return $db->fetchAll($sql);
	}   
}