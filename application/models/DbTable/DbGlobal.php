<?php

class Application_Model_DbTable_DbGlobal extends Zend_Db_Table_Abstract
{
    // set name value
// 	public function setName($name){
// 		$this->_name=$name;
// 	}
	protected $_name = 'tb_purchase_order';
	/**
	 * get selected record of $sql
	 * @param string $sql
	 * @return array $row;
	 */
	 function getDnNo($completed=null,$opt=null){
   	$db= $this->getAdapter();
	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$sql="SELECT r.`order_id`,r.`dn_number`,r.`recieve_number` FROM `tb_recieve_order` AS r WHERE 1";
   	if($completed!=null){
   		$sql.="  AND r.is_invoice_controlling=0 ";
   	}
   	$sql.=" ORDER BY r.order_id DESC ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array(-1=>$tr->translate("SELECT_DN"));
   		if(!empty($row)) foreach($row as $read) $options[$read['order_id']]=$read['dn_number']."(".$read['recieve_number'].")";
   		return $options;
   	}
   }   
	 public function getPuInvoiceNumber($branch_id = 1){
    	$this->_name='tb_purchase_invoice';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
    	$pre = $this->getPrefixCode($branch_id)."IV";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
	 function getBranch(){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql = "SELECT l.id,l.`name` FROM `tb_sublocation` AS l WHERE l.`status`=1";
		$location = $db_globle->getAccessPermission('l.`id`');
		return $db->fetchAll($sql.$location);
	}
	function getPurchasePedding(){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql = "SELECT v.`key_code` as id,v.`name_en` as name FROM `tb_view` AS v WHERE v.`type`=11 AND v.`status`=1 AND v.`key_code`!=0 ORDER BY v.`key_code`";
		return $db->fetchAll($sql);
	}
	 function getRejectExist($id,$type){
		$db=$this->getAdapter();
		$sql="SELECT p.`re_id`,p.type FROM `tb_purchase_request_remark` AS p WHERE p.re_id=$id AND p.type=$type";
		$row = $db->fetchRow($sql);
    	return $row;  
	}
	function getPriceCompareRejectExist($id,$type){
		$db=$this->getAdapter();
		$sql="SELECT p.`re_id`,p.type FROM `tb_price_compare_remark` AS p WHERE p.re_id=$id AND p.type=$type";
		$row = $db->fetchRow($sql);
    	return $row;  
	}
	
	function getSaleRejectExist($id,$type){
		$db=$this->getAdapter();
		$sql="SELECT p.`re_id`,p.type FROM `tb_sale_request_remark` AS p WHERE p.re_id=$id AND p.type=$type";
		$row = $db->fetchRow($sql);
    	return $row;  
	}
	 public function getRequestNumber($branch_id = 1){
    	$this->_name='tb_sales_order';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." AND type=2 LIMIT 1 ";
    	$pre = $this->getPrefixCode($branch_id)."RO-";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
	 public function getAllStaff(){
		$db=$this->getAdapter();
		$sql = "SELECT s.id,s.`name` FROM `tb_staff` AS s WHERE s.`status`=1";
  		$row=$db->fetchAll($sql);
  		return $row;
	}
	public function getAllStaffByID($id){
		$db=$this->getAdapter();
		$sql = "SELECT s.`position` FROM `tb_staff` AS s WHERE s.`id`=$id";
  		$row=$db->fetchOne($sql);
  		return $row;
	}
	
	function getAllWorkPlanByID($id,$opt=null){
		$db=$this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="SELECT p.`id`,p.`name` FROM `tb_work_plan` AS p WHERE p.`plan_id`=$id";
		///echo $sql;
		$row = $db->fetchAll($sql);
		if($opt==null){
			return $row;
		}else{
			$options='<option value="">'.$tr->translate("SELECT").'</option>';
			//$options=array(0=>  $tr->translate("SELECT"));
			if(!empty($row)){ foreach($row as $key=> $rs){
				$options .= '<option value="'.$rs['id'].'" >'.($key+1)." - ".htmlspecialchars($rs['name'], ENT_QUOTES)
    					.'</option>';
			}}
			return $options;
		}
		
		//echo $options;
    }
	 public function getTitleReportold($id){
		$db=$this->getAdapter();
		$sql="SELECT 
				  s.`name`,
				  s.`prefix`,
				  s.`logo`,
				  s.`title_report_en`,
				  s.`title_report_kh`
				FROM
				  `tb_location` AS s 
				WHERE s.id =$id";
		$row = $db->fetchRow($sql);
    	return $row;  
	}
	public function getTitleReport($id){
		$db=$this->getAdapter();
		$sql="SELECT 
				  s.`name`,
				  s.`branch_code` as title_report_en,
				   s.`branch_code` as title_report_kh,
				  s.`prefix`,
				  s.`logo`,
				  s.`address`,
				  s.`phone`,
				  s.`contact`,
				  s.`email`
				FROM
				  `tb_sublocation` AS s 
				WHERE s.id =$id";
		$row = $db->fetchRow($sql);
    	return $row;  
	}
	public function getGlobalDb($sql)
  	{
  		$db=$this->getAdapter();
  		$row=$db->fetchAll($sql);
  		if(!$row) return NULL;
  		return $row;
  	}
  	public function getGlobalDbRow($sql)
  	{
  		$db=$this->getAdapter();
  		$row=$db->fetchRow($sql);
  		if(!$row) return NULL;
  		return $row;
  	}
  	
  	public static function getActionAccess($action)
    {
    	$arr=explode('-', $action);
    	return $arr[0];    	
    }     
    
    /**
     * get CSO options for select box
     * @return array $options
     */
    public function getOptionCSO(){
    	$options = array('Please select');
    	$sql = "SELECT id, name_en FROM fi_cso ORDER BY name_en";
    	$rows = $this->getGlobalDb($sql);
    	foreach($rows as $ele){
    		$options[$ele['id']] = $ele['name_en'];
    	}
    	return $options;
    }
    
    /**
     * boolean true mean record exist already
     * @param string $conditions
     * @param string $tbl_name
     * @return boolean
     */
    public function isRecordExist($conditions,$tbl_name){
		$db=$this->getAdapter();
		$sql="SELECT * FROM ".$tbl_name." WHERE ".$conditions; 
		$stm = $db->query($sql);
		$row = $stm->fetchAll();
    	if(!$row) return false;
    	return true;    	
    }
    
    public function getDeliverProductExist($id_order_update){
    	$db= $this->getAdapter();
    	$sql="SELECT 
						  SUM(so.`qty_delivery`) AS qty,
						  soi.qty_order,
						  (SELECT p.pro_id FROM tb_product AS p WHERE p.pro_id = so.`pro_id`) AS pro_id,
						  (SELECT p.qty_onhand FROM tb_product AS p WHERE p.pro_id = so.`pro_id`) AS qty_onhand,
						  (SELECT p.qty_available FROM tb_product AS p WHERE p.pro_id = so.`pro_id`) AS qty_available,
						  (SELECT p.qty_onsold  FROM tb_product AS p WHERE p.pro_id = so.`pro_id`) AS qty_onsold 
						FROM
						  tb_sale_order_delivery AS so ,
						  `tb_boq_item` AS soi
						WHERE so.sale_order_id = $id_order_update 
						AND so.`pro_id`=soi.`pro_id`
						AND so.`sale_order_id`=soi.`order_id`
						GROUP BY so.pro_id ,soi.`pro_id`";
    	$rows= $db->fetchAll($sql);
    	return $rows; 
    }
    public function porductLocationExist($pro_id, $location_id){//used
    	$db=$this->getAdapter();
    	
    	$sql="SELECT ProLocationID,qty,qty_onorder,qty_avaliable,LocationId,pro_id,qty_onsold FROM tb_prolocation WHERE pro_id = ".$pro_id." AND LocationId = ".$location_id." LIMIT 1";
    	try{
    	$row = $db->fetchRow($sql);
    	}catch (Exception $e){
    		var_dump($sql);
    		die($e->getMessage());
    	}
    	//echo $sql;exit();
    	
    	return $row;
    }
    //get value in product inventory with product location (Joint)
    
    public function productLocationInventory($pro_id, $location_id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,pro_id,location_id,qty,qty_warning,user_id,last_mod_date,last_mod_userid
    	 FROM tb_prolocation WHERE pro_id =".$pro_id." AND location_id=".$location_id." LIMIT 1 "; 

    	
    	$row = $db->fetchRow($sql);
    	
    	if(empty($row)){
    		$session_user=new Zend_Session_Namespace('auth');
    		$userName=$session_user->user_name;
    		$GetUserId= $session_user->user_id;
    		
    		$array = array(
    				"pro_id"=>$pro_id,
    				"location_id"=>$location_id,
    				"qty"=>0,
    				"qty_warning"=>0,
    				"last_mod_userid"=>$GetUserId,
    				"user_id"=>$GetUserId,
    				"last_mod_date"=>date("Y-m-d")
    				);
    		$this->_name="tb_prolocation";
    		$this->insert($array);
    		
    		$sql="SELECT id,pro_id,location_id,qty,qty_warning,user_id,last_mod_date,last_mod_userid
    		FROM tb_prolocation WHERE pro_id =".$pro_id." AND location_id=".$location_id." LIMIT 1 ";
    		return $row = $db->fetchRow($sql);
    	}else{
    		
    	return $row; 
    	}  	
    }
    //for get product product inventory but if have in prodcut location
    public function productInventoryExist($itemId){
    	$db=$this->getAdapter();
    	$sql="SELECT pl.ProLocationID, pl.qty, iv.QuantityOnHand, iv.QuantityAvailable
				FROM tb_prolocation AS pl
				INNER JOIN tb_inventorytotal AS iv ON iv.ProdId = pl.pro_id WHERE pl.pro_id=".$itemId." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	if(!$row) return false;
    	return $row;    	
    }
    
    //to get and check if product total inventory exist 8/26/13
    public function InventoryExist($pro_id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM tb_product WHERE pro_id =".$pro_id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	if(!$row) return false;
    	return $row;
    }
    public function productLocation($pro_id,$location_id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM tb_prolocation WHERE pro_id =".$pro_id." AND LocationId = ".$location_id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	if(!$row) return false;
    	return $row;
    }
    public function QtyProLocation($pro_id,$location_id){//get qty location
    	$db=$this->getAdapter();
    	$sql="SELECT ProLocationID,pro_id,qty FROM tb_prolocation WHERE pro_id =".$pro_id." AND LocationId = ".$location_id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
	//if myProductExist
    public function myProductExist($pro_id){
    	$db=$this->getAdapter();
    	$sql="SELECT pro_id FROM tb_product WHERE pro_id =".$pro_id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    
    public function userSaleOrderExist($order_id , $location_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT order_id FROM tb_boq WHERE order_id =".$order_id." AND LocationId = $location_id LIMIT 1";
    	$row= $db->fetchRow($sql);
    	return $row;
    }
    
    public function userPurchaseOrderExist($order_id , $location_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT order_id FROM tb_purchase_order WHERE order_id =".$order_id." AND LocationId = $location_id LIMIT 1";
    	$row= $db->fetchRow($sql);
    	return $row;
    }
    //if user purchase exist
    public function userPurchaseExist($order_id , $location_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT order_id FROM tb_purchase_order WHERE order_id =".$order_id." AND LocationId = $location_id"." LIMIT 1";
    	$row= $db->fetchRow($sql);
    	return $row;
    }  
    //check product location history exit(for update prodcut) 28/8
    public function prodcutHistoryExist($location_id,$id){
    	$db=$this->getAdapter();
    	$sql="SELECT pl.ProLocationID, pl.qty FROM tb_prolocation_history AS pl
    	INNER JOIN tb_product AS p ON p.pro_id = pl.pro_id
    	WHERE pl.LocationId = ".$location_id." AND p.pro_id=".$id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    //check if not have in product location history
    public function proLocationHistoryNoExist($id){
    	$db=$this->getAdapter();
    	$sql="SELECT qty,locationID FROM tb_prolocation_history
    	WHERE pro_id= $id AND LocationId NOT IN
    	( SELECT LocationId FROM tb_prolocation where pro_id = $id) ";
    	$row = $db->fetchAll($sql);
    	if(!$row) return false;
    	return $row;
    	
    }
    //for check order history exist 
    final public function orderHistoryExitRow($order_id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM `tb_order_history` WHERE `order`= ".$order_id." LIMIT 1";
    	$row=$db->fetchRow($sql);
    	return $row;
    	
    }
    final public function purchaseOrderHistoryExitAll($order_id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM `tb_purchase_order_history` WHERE type=1 AND `order`=". $order_id;
    	$rows=$db->fetchAll($sql);
    	//if(!$rows) return false;
    	return $rows;
    	 
    }
    final public function purchaseOrderHistory($order_id){
    	$db=$this->getAdapter();
   	$sql="SELECT * FROM `tb_purchase_order_history` WHERE type=1 AND `order`=". $order_id;
    	$rows=$db->fetchAll($sql);
    	//if(!$rows) return false;
    	return $rows;
    
    }
    final public function salesOrderHistoryExitAll($order_id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM `tb_order_history` WHERE type=2 AND `order`= ".$order_id;
    	$rows=$db->fetchAll($sql);
    	if(!$rows) return false;
    	return $rows;
    
    }
    final public function inventoryLocation($locationid, $itemId){
    	$db=$this->getAdapter();
    	$sql="SELECT pl.ProLocationID, pl.`qty_onorder` ,pl.qty, p.qty_onhand,p.qty_available
    	FROM tb_prolocation AS pl
    	INNER JOIN tb_product AS p ON p.pro_id = pl.pro_id
    	WHERE pl.LocationId = ".$locationid. " AND pl.pro_id= ".$itemId." LIMIT 1";
    	$row=$db->fetchRow($sql);
    	return $row;
    }
    final public function productInvetoryLocation($locationid, $itemId){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    			p.pro_id,
    			pl.ProLocationID, 
    			pl.`qty_onorder`,
    			pl.qty_onsold as prol_qty_onsold ,
    			pl.qty,
    			pl.qty_avaliable,
    			p.qty_onsold,
    			p.qty_onorder as pro_qty_onorder, 
    			p.qty_onhand,
    			p.qty_available
    			
    	FROM tb_prolocation AS pl
    	INNER JOIN tb_product AS p ON p.pro_id = pl.pro_id
    	WHERE pl.LocationId = ".$locationid. " AND pl.pro_id= ".$itemId." LIMIT 1";
    	
//     	$sql="SELECT pl.ProLocationID, pl.`qty_onorder` ,pl.qty, p.qty_onhand,p.qty_available
//     	FROM tb_prolocation AS pl
//     	INNER JOIN tb_product AS p ON p.pro_id = pl.pro_id
//     	WHERE pl.LocationId = ".$locationid. " AND pl.pro_id= ".$itemId." LIMIT 1";
    	$row=$db->fetchRow($sql);
    	return $row;
    }
    
    
    
    /**
     * insert record to table $tbl_name
     * @param array $data
     * @param string $tbl_name
     */
    public function addRecord($data,$tbl_name){
    	$this->setName($tbl_name);
    	return $this->insert($data);
    }
    
    
    /**
     * update record to table $tbl_name
     * @param array $data
     * @param int $id
     * @param string $tbl_name
     */
	public function updateRecord($data,$id,$updateby,$tbl_name){
		$tb = $this->setName($tbl_name);
		$where=$this->getAdapter()->quoteInto($updateby.'=?',$id);
		$rs = $this->update($data,$where);
		//echo $rs;//exit();
		
	}
    
    public function DeleteRecord($tbl_name,$id){
    	$db = $this->getAdapter();
		$sql = "UPDATE ".$tbl_name." SET status=0 WHERE id=".$id;
		return $db->query($sql);
    }

    public function deleteRecords($sql){
    	$db = $this->getAdapter();
		return $db->query($sql);
    } 

     public function DeleteData($tbl_name,$where){
    	$db = $this->getAdapter();
		$sql = "DELETE FROM ".$tbl_name.$where;
		return $db->query($sql);
    } 
    
    public function convertStringToDate($date, $format = "Y-m-d H:i:s")
    {
    	if(empty($date)) return NULL;
    	$time = strtotime($date);
    	return date($format, $time);
    }
    /* @Desc: add or sub qty of item depend on item and stock
     * @param $stockID stock id
     * @param $itemQtys array of item id and item qty
     * @param $sign: + | -
     * */
    public function query($sql){
    	$db = $this->getAdapter();
    	return $db->query($sql);	
    }
    public function fetchArray($result){
    	$db = $this->getAdapter();
    	return mysql_fetch_assoc($result);
    }
    public function getUserInfo(){
    	$session_user=new Zend_Session_Namespace('auth');
    	$userName=$session_user->user_name;
    	$GetUserId= $session_user->user_id;
    	$level = $session_user->level;
    	$location_id = $session_user->location_id;
    	$info = array("user_name"=>$userName,"user_id"=>$GetUserId,"level"=>$level,"branch_id"=>$location_id);
    	return $info;
    }
    
    public function getAccessPermission($branch='branch_id'){
    	$result = $this->getUserInfo();
    	if($result['level']==1){
    		$result = "";
    		return $result;
    	}
    	else{
    		$sql_string = $this->getAllLocationByUser($result['user_id'],$branch);
    		$result = " AND (".$sql_string.")";
    		return $result;
    	} 
    }
	 function getAllLocation($opt=null){
   		   		$db=$this->getAdapter();
   		$sql=" SELECT id,`name` FROM `tb_sublocation` WHERE `name`!='' AND status=1  ";
//    		$sql.=$this->getAccessPermission("id");
   		$result = $this->getUserInfo();
   		$sql.= " AND ".$this->getAllLocationByUser($result['user_id']);
   		$row =  $db->fetchAll($sql);
   		if($opt==null){
   			return $row;
   		}else{
   			$options=array();
			$request=Zend_Controller_Front::getInstance()->getRequest();
   			$module = $request->getModuleName();
   			if($module=='report'){
   				$options=array(-1=>"Select Location");
   			}
   			if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['name'];
   			return $options;
   		}
   }
     function getAllLocationByUser($user_id,$branch_name='id'){
    	$db = $this->getAdapter();
    	$result = $this->getUserInfo();
    	if($result['level']==1){
    		return " 1 ";
    	}
    	$sql=" SELECT * FROM `tb_acl_ubranch` WHERE user_id=$user_id ";
    	
    	
    	$rows = $db->fetchAll($sql);
    	$s_where = array();
    	$where='';
    	if(!empty($rows)){
    		foreach ($rows as $rs){
    			$s_where[] = $branch_name." = {$rs['location_id']}";
    		}
    		$where .=' '.implode(' OR ',$s_where).'';
    	}
    	return $where;
    }
   
    public function getSetting(){
    	$DB = $this->getAdapter();
    	$sql="SELECT * FROM `tb_setting` ";
    	RETURN $DB->fetchAll($sql);
    }
    public static function GlobalgetUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
	
    public static function writeMessageErr($err=null)
    {
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$action=$request->getActionName();
    	$controller=$request->getControllerName();
    	$module=$request->getModuleName();
    
    	$session = new Zend_Session_Namespace('auth');
    	$user_name = $session->user_name;
    
    	$file = "../logs/error.log";
    	if (!file_exists($file)) touch($file);
    	$Handle = fopen($file, 'a');
    	$stringData = "[".date("Y-m-d H:i:s")."]"." [user]:".$user_name." [module]:".$module." [controller]:".$controller. " [action]:".$action." [Error]:".$err. "\n";
    	fwrite($Handle, $stringData);
    	fclose($Handle);
    
    }
    
    // ****************** Check Product Location  **************************
    public function productLocationInventoryCheck($pro_id, $location_id){
    	$db=$this->getAdapter();
    	$sql="SELECT pl.ProLocationID, pl.qty, p.qty_onorder
			FROM tb_prolocation AS pl
			INNER JOIN tb_product AS p ON p.pro_id = pl.pro_id
			WHERE pl.LocationId =".$location_id." AND pl.pro_id = ".$pro_id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    public function getQtyFromProductById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT `qty_onhand`,`qty_onsold`,`qty_onorder`,`qty_available`
    	      FROM `tb_product` where `pro_id`= ".$db->quote($id);
    	return $db->fetchRow($sql);
    }
    public function getSettingById($id){
    	$sql = "SELECT CODE,key_name,key_value FROM tb_setting where code = ".$id;
    	return $this->getAdapter()->fetchRow($sql);
    }
    public function getMeasureById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT `qty_perunit` FROM tb_product WHERE pro_id= '$item_id' LIMIT 1 ";
    }
    public function getQuoationNumber($branch_id = 1){
    	$this->_name='tb_quoatation';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
    	$pre = $this->getPrefixCode($branch_id)."Q";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    public function getSalesNumber($branch_id = 1){
    	$this->_name='tb_boq';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
    	$pre = $this->getPrefixCode($branch_id)."SO";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
	public function getInvoiceNumber($branch_id = 1){
    	$this->_name='tb_invoice';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
    	$pre = $this->getPrefixCode($branch_id)."IV";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    function getPrefixCode($branch_id){
    	$db  = $this->getAdapter();
    	$sql = " SELECT prefix FROM `tb_sublocation` WHERE id = $branch_id  LIMIT 1";
    	return $db->fetchOne($sql);
    }
    function getAllTermCondition($opt=null,$type=null,$defual=null){
    	$db = $this->getAdapter();
    	$sql = " SELECT id,id AS condition_id,con_khmer,con_english,is_default FROM `tb_termcondition` WHERE con_khmer!='' AND status = 1 ";
    	if($type!=null){
    		$sql.=" AND type = $type";
    	}
		if($defual!=null){
			$sql.=" AND is_default = 1";
		}
    	$rows =  $db->fetchAll($sql);
    	if($opt!=null){
    		$option='';
    		if(!empty($rows)){foreach ($rows as $key =>$rs){ 
    			$option .= '<option value="'.$rs['id'].'" >'.($key+1)." - ".htmlspecialchars($rs['con_khmer'], ENT_QUOTES)
    					.'</option>';
    		}
    		return $option;
    	  }
    	}else{
    		return $rows;
    	}
    }
   function getProductPriceBytype($customer_id,$product_id){//used BY Customer Level and Product id
   	$db = $this->getAdapter();
   	$sql=" SELECT price,id FROM `tb_product` WHERE id=$product_id LIMIT 1 ";
   	return $db->fetchRow($sql);
   }
   function getTermConditionById($term_type,$record_id=null){
   	$db = $this->getAdapter();
   	$sql=" SELECT t.id AS condition_id,t.con_khmer,t.con_english FROM `tb_termcondition` AS t,`tb_quoatation_termcondition` AS tc 
   		WHERE t.id=tc.condition_id AND tc.term_type=$term_type ";
		if($record_id!=null){$sql.=" AND quoation_id=$record_id ";}
   	return $db->fetchAll($sql); 
   }
   function getTermConditionByIdIinvocie($term_type,$record_id=null){
   	$db = $this->getAdapter();
   	$sql=" SELECT t.con_khmer,t.con_english FROM `tb_termcondition` AS t
   		WHERE t.type=$term_type ";
		
   	return $db->fetchAll($sql); 
   }
   function getAllInvoice($completed=null,$opt=null){
   		$db= $this->getAdapter();
   	$sql=" SELECT id,invoice_no FROM `tb_invoice` WHERE status=1  ";
   	if($completed!=null){ $sql.="  AND is_fullpaid=0 ";} 
   	$sql.=" ORDER BY id DESC ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		  $options=array(-1=>"Select Invoice");
   		 if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['invoice_no'];
   		 return $options;
   	}
   }
 
   function getAllInvoicePayment($post_id,$type){
   	$db= $this->getAdapter();
	if($type==1){//by customer
		$sql=" SELECT *,v.id AS invoice_id FROM `tb_invoice` AS v,tb_boq as s WHERE v.sale_id = s.id AND s.customer_id = $post_id AND v.status=1  ";
		$sql.="  AND v.is_fullpaid=0 ";
		$sql.=" ORDER BY v.id DESC ";
   }else{//by invoice
		$sql=" SELECT v.*,v.id AS invoice_id,
		(SELECT s.customer_id FROM `tb_boq` AS s WHERE s.id=v.sale_id) AS customer_id
		FROM `tb_invoice` AS v  WHERE v.id=$post_id AND v.status=1  ";
		$sql.="  AND v.is_fullpaid=0 LIMIT 1";
	}
   	return  $db->fetchAll($sql);
   }
   	function getAllCustomer($opt=null){
   		$db=$this->getAdapter();
   		$sql=" SELECT id, CONCAT(cust_name,',',contact_name) AS cust_name,cu_code FROM tb_customer WHERE 
   		 status=1 AND (cust_name!='' OR contact_name!='' OR cu_code!='') ORDER BY id DESC ";
   		
   		$row =  $db->fetchAll($sql);
   		if($opt==null){
   			return $row;
   		}else{
   			//$options=array(0=>"Select Customer",-1=>"Add New Customer");
   			$options=array(0=>"Select Customer",);
   			if(!empty($row)) foreach($row as $read) $options[$read['id']]=str_replace("-","",$read['cust_name']).'-'.$read['cu_code'];
   			return $options;
   		}
   }
   function getAllCustomerPayment($opt=null){
    	$db=$this->getAdapter();
    	$sql=" SELECT DISTINCT (c.id) AS id,
    	CONCAT(c.cust_name,',',c.contact_name) AS cust_name
    	FROM `tb_invoice` AS iv,`tb_boq` AS s,tb_customer AS c
    	WHERE iv.sale_id=s.id AND c.id=s.`customer_id` AND iv.is_fullpaid=0";
    	$row =  $db->fetchAll($sql);
    	if($opt==null){
    		return $row;
    	}else{
    		//$options=array(0=>"Select Customer",-1=>"Add New Customer");
    		$options=array(0=>"Select Customer",);
    		if(!empty($row)) foreach($row as $read) $options[$read['id']]=str_replace("-","",$read['cust_name']);
    		return $options;
    	}
    }
   	function getAllProvince($opt=null){
   		$db=$this->getAdapter();
   		$sql=" SELECT province_id,province_en_name FROM ln_province WHERE province_en_name!='' ";
   		
   		$row =  $db->fetchAll($sql);
   		if($opt==null){
   			return $row;
   		}else{
   			$options=array();
   			if(!empty($row)) foreach($row as $read) $options[$read['province_id']]=str_replace("-","",$read['province_en_name']);
   			return $options;
   		}
   }
   
   function getAllCurrency($opt=null){
   	$db=$this->getAdapter();
   	$sql=" SELECT id, description,symbal FROM tb_currency WHERE status = 1 ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array();
   		if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['description'].$read['symbal'];
   		return $options;
   	}
   }
   function getAllVendor($opt=null){
   	$db=$this->getAdapter();
   	$sql=" SELECT vendor_id, v_name FROM tb_vendor WHERE v_name!='' AND status = 1 ORDER BY vendor_id DESC";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array(0=>"Select Vendor",-1=>"Add Vendor");
   		if(!empty($row)) foreach($row as $read) $options[$read['vendor_id']]=$read['v_name'];
   		return $options;
   	}
   }
   function getAllPaymentmethod($opt=null){
   	$db=$this->getAdapter();
   	$sql=" SELECT * FROM tb_paymentmethod ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array();
   		if(!empty($row)) foreach($row as $read) $options[$read['payment_typeId']]=$read['payment_name'];
   		return $options;
   	}
   }
    function getAllExpense($opt=null){
   	$db=$this->getAdapter();
   	$sql=" SELECT * FROM tb_expensetitle where status=1 and title!='' ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array(0=>"Select Expense",-1=>"Add New Expense Title");
   		if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['title'];
   		return $options;
   	}
   }
   function getAllExpensePu($opt=null){
   	$db=$this->getAdapter();
   	$sql=" SELECT * FROM tb_expensetitle where status=1 and title!='' ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array(0=>"Select Expense",-1=>"Add New Expense Title");
   		if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['title'];
   		return $options;
   	}
   }
   public function getReceiptNumber($branch_id = 1){
    	$this->_name='tb_receipt';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
    	$pre = $this->getPrefixCode($branch_id)."R";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
	
	function getSaleAgent($option=null){
    	$db = $this->getAdapter();
    	$sql=' SELECT id ,name FROM `tb_sale_agent` WHERE name!="" AND status=1  ';
    	$result = $this->getUserInfo();
    	if($result['level']==6){
    		$sql.=" AND acl_user = ".$result['user_id'];
    	}
    	$rows = $db->fetchAll($sql);
    	if($option!=null){
    	  $opt=array();  
    	  if(!empty($rows)) {
    		foreach($rows as $rs){
    			$opt[$rs['id']]=$rs['name'];
    		}
    	  }
    	  return $opt;
    	}else{
    		return $rows;
    	}
    }
	function getUpdatetermcustomer(){
    	$db = $this->getAdapter();
    	$sql=" SELECT v.`credit_limit`,v.`credit_term`,v.key_code FROM `tb_view` AS v WHERE v.`type`=6 ";
    	$row = $db->fetchAll($sql);
    	if(!empty($row)){
    		$this->_name='tb_customer';
    		foreach ($row as $rs){
    			$arr = array(
    					'credit_team'=>$rs['credit_term'],
						'credit_limit'=>$rs['credit_limit']
    					); 
    			$where = " cu_type=".$rs['key_code']." AND credit_team='0.00' ";
    			$this->update($arr, $where);
    		}
    	}
    }
	function getAllInvoicePO($completed=null,$opt=null){
   	$db= $this->getAdapter();
	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$sql="SELECT i.`id`,i.`invoice_no`,i.`invoice_controlling_date`,i.`receive_invoice_date`,i.`invoice_date` FROM `tb_invoice_controlling` AS i WHERE 1";
   	if($completed!=null){
   		$sql.="  AND i.is_completed=0 ";
   	}
   	$sql.=" ORDER BY id DESC ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array(-1=>$tr->translate("SELECT_INVOICE"));
   		if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['invoice_no'];
   		return $options;
   	}
   }
   function getAllInvoicePaymentPurchase($post_id,$type){
   	$db= $this->getAdapter();
   	if($type==1){//by customer
   		$sql=" SELECT 
				  i.invoice_id as id,
				  i.`invoice_no`,
				  i.`invoice_controlling_date`,
				  i.`receive_invoice_date`,
				  i.`invoice_date` ,
				  i.`grand_total`,
				  i.grand_total_after,
				  i.paid,
				  i.balance,
				  i.`sub_total`,
				  i.vat,
				  i.total_vat,
				  i.vendor_id
				FROM
				  `tb_invoice_controlling` AS i 
				WHERE i.`vendor_id`=$post_id";
   		$sql.=" AND is_completed=0";
   		$sql.=" ORDER BY id DESC ";
   	}else{//by invoice
   		$sql=" SELECT 
				  i.`id`,
				  i.`invoice_no`,
				  i.`invoice_controlling_date`,
				  i.`receive_invoice_date`,
				  i.`invoice_date` ,
				  i.`grand_total`,
				  i.grand_total_after,
				  i.paid,
				   i.`sub_total`,
				   i.vat,
				  i.total_vat,
				  i.balance,
				  i.vendor_id
				FROM
				  `tb_invoice_controlling` AS i 
				WHERE i.`id`=$post_id";
   		$sql.=" AND is_completed=0 LIMIT 1 ";
   	}
   	return  $db->fetchAll($sql);
   }
   function getAllPlan($opt=null){
   	$db= $this->getAdapter();
   	$sql=" SELECT id,`name` FROM `tb_plan` WHERE STATUS=1 AND `name`!='' ";
   	$sql.=" ORDER BY id DESC ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array(-1=>"Select Plan");
   		if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['name'];
   		return $options;
   	}
   }
}
?>