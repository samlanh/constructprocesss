<?php

class Sales_Model_DbTable_Dbcost extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_boqcost";
	function getAllSaleOrder($search){
			$db= $this->getAdapter();
			$sql=" SELECT id,
			(SELECT NAME FROM `tb_plan` WHERE id = tb_boqcost.project_name limit 1 ) as project_name,
			(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=tb_boqcost.customer_id LIMIT 1 ) AS customer_name,
			(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=tb_boqcost.customer_id LIMIT 1 ) AS contact_name,	
			(SELECT NAME FROM `tb_sale_agent` WHERE tb_sale_agent.id =tb_boqcost.saleagent_id  LIMIT 1 ) AS staff_name,
			boq_number,duration,(SELECT name_en FROM `tb_view` WHERE TYPE=2 AND key_code=project_type) AS project_type 
			,costlaborprice,costmaterail,all_total,boq_date,
			(SELECT name_en FROM `tb_view` WHERE TYPE=3 AND key_code=is_approved LIMIT 1) AS approval,
			(SELECT name_en FROM `tb_view` WHERE TYPE=4 AND key_code=pending_status LIMIT 1) AS processing,
			(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = tb_boqcost.user_id LIMIT 1) AS user_name
			FROM `tb_boqcost` ";
			
			$from_date =(empty($search['start_date']))? '1': " boq_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " boq_date <= '".$search['end_date']." 23:59:59'";
			$where = " WHERE ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " boq_number LIKE '%{$s_search}%'";
				$s_where[] = " net_total LIKE '%{$s_search}%'";
				$s_where[] = " paid LIKE '%{$s_search}%'";
				$s_where[] = " balance LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
// 			if($search['branch_id']>0){
// 				$where .= " AND branch_id = ".$search['branch_id'];
// 			}
			if($search['customer_id']>0){
				$where .= " AND customer_id =".$search['customer_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
			return $db->fetchAll($sql.$where.$order);
	}
	public function addNewSaleCost($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
			$this->_name='tb_plan';
			$arr =array("pedding"=>4,);
			$where = " id =".$data['project_name'];
			$this->update($arr, $where);
			
			
			$data["branch_id"]=1;
			//$so = $dbc->getSalesNumber($data["branch_id"]);

			$info_purchase_order=array(
					"customer_id"    => $data['customer_id'],
					"branch_id"      => 1,
					"project_name"   => $data['project_name'],
					"project_address" => $data['project_address'],
					"boq_number"     => $data['txt_order'],
					"create_date"    => date("Y-m-d"),
					"boq_date"       => date("Y-m-d",strtotime($data['boq_date'])),
					//"start_date"     => date("Y-m-d",strtotime($data['start_date'])),
					//"end_date"       => date("Y-m-d",strtotime($data['end_date'])),
					"duration"       => $data['duration'],
					"project_type"   => $data['project_type'],
					"saleagent_id"   => $data['saleagent_id'],
					"remark"         => $data['remark'],
					"all_total"      => $data['all_total'],
					"costlaborprice" => $data['total_labor'],
					"costmaterail" => $data['total_materail'],
					"labour_note"	 => $data["labor_remark"],
					"user_id"        => $GetUserId,
					'pending_status' => 4,);
			$this->_name="tb_boqcost";
			$sale_id = $this->insert($info_purchase_order); 
			unset($info_purchase_order);

			$ids=explode(',',$data['identity']);
			$locationid=$data['branch_id'];
			$parent_service=0;
			foreach ($ids as $i)
			{
				if($data['is_service'.$i]==1){
					$parent_service = $data['item_id_'.$i];
					$data['qty'.$i]=0;
					$data['material_price'.$i]=0;
					$data['labour_price'.$i]=0;
					$data['material_price'.$i]=0;
					$data['oldprice_'.$i]=0;
					$data['total'.$i]=0;
					$data['measure'.$i]='';
				}
				$data_item= array(
					'boq_id'=> $sale_id,
					'pro_id'	  => $data['item_id_'.$i],
					'measure'=>$data['measure'.$i],
					'qty_order'	  => $data['qty'.$i],
					'costlabour_price'=> $data['labour_price'.$i],
					'totalcostlabor'=> $data['total_labor'.$i],
					'costmaterail_price'=> $data['material_price'.$i],
					'totalcostmaterail'=> $data['material_price'.$i],					
					'sub_total'	  => $data['total'.$i],
					'is_service'  =>$data['is_service'.$i],
				    'parent_service'=>$parent_service,
				);
				$this->_name='tb_boqcostdetail';
				$this->insert($data_item);
			 }
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err= $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	public function RejectSale($data)
	{
		$id=$data["id"];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			if($data['approved_name']==2){//reject sale update to quoatation
					$dbc=new Application_Model_DbTable_DbGlobal();
				    $pending=1;
					$arr=array(
							'is_approved'	=> $data['approved_name'],
							'approved_userid'=> $GetUserId,
							'approval_note'	=> $data['app_remark'],
							'approved_date'	=> date("Y-m-d",strtotime($data['app_date'])),
							'pending_status'=>$pending,
					);
					$this->_name="tb_quoatation";
					$where = " id = ".$data["id"];
					$sale_id = $this->update($arr, $where);
			}else{//can sale quoation or sale;
				$arr = array(
						'is_cancel'=>1,
						'cancel_comment'=>$data['app_remark'],
						'cancel_date'=>date("Y-m-d",strtotime($data['app_date'])),
						'cancel_user'=>$GetUserId,
				);
				$where=" id=".$data['quote_id'];
				if(!empty($data['quote_id'])){
					$this->_name="tb_quoatation";
					$this->update($arr, $where);
				}
				
				$this->_name="tb_boq";
				$where=" id=".$data['id'];
				$this->update($arr, $where);
// 				print_r($arr);exit();
				
			}
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	public function updateSaleOrder($data)
	{
		$id=$data["id"];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
// 			$so = $dbc->getSalesNumber($data["branch_id"]);
			$arr=array(
					"customer_id"   => 	$data['customer_id'],
					"branch_id"     => 	$data["branch_id"],
// 					"sale_no"       => 	$so,//$data['txt_order'],
					"date_sold"     => 	date("Y-m-d",strtotime($data['order_date'])),
					"saleagent_id"  => 	$data['saleagent_id'],
					"currency_id"    => $data['currency'],
					"remark"         => 	$data['remark'],
					"all_total"      => 	$data['totalAmoun'],
					"discount_value" => 	$data['dis_value'],
					"discount_type"  => 	$data['discount_type'],
					"net_total"      => 	$data['all_total'],
					"user_mod"       => 	$GetUserId,
					"is_cancel"       => 	0,
 					'pending_status' =>2,
					'is_approved'=>0,
					'is_toinvocie'=>0,
					"date"      => 	date("Y-m-d"),
			);

			$this->_name="tb_boq";
			$where="id = ".$id;
			$this->update($arr, $where);
			unset($arr);
			
			$this->_name='tb_boqdetail';
			$where = " saleorder_id =".$id;
			$this->delete($where);
			
			$ids=explode(',',$data['identity']);
			$locationid=$data['branch_id'];
			foreach ($ids as $i)
			{
				$data_item= array(
						'saleorder_id'=> $id,
						'pro_id'	  => 	$data['item_id_'.$i],
						'qty_unit'=>$data['qty_unit_'.$i],
						'qty_detail'  => 	$data['qty_per_unit_'.$i],
						'qty_order'	  => 	$data['qty'.$i],
						'price'		  => 	$data['price'.$i]+$data['extra_price'.$i],
						'extra_price' =>    $data['extra_price'.$i],
						'cost_price'   =>    $data['cost_price_'.$i],
						'old_price'   =>    $data['oldprice_'.$i],
						'disc_value'  =>    str_replace("%",'',$data['dis_value'.$i]),//check it
						'disc_type'	  =>    $data['discount_type'.$i],//check it
						'sub_total'	  => $data['total'.$i],
				);
				$this->_name='tb_boqdetail';
				$this->insert($data_item);
// 				print_r($data_item);exit();
			}
			$this->_name='tb_quoatation_termcondition';
			$where = " term_type=2 AND quoation_id = ".$id;
			$this->delete($where);
			
			$ids=explode(',',$data['identity_term']);
			if(!empty($data['identity_term'])){
				foreach ($ids as $i)
				{
					$data_item= array(
							'quoation_id'=> $id,
							'condition_id'=> $data['termid_'.$i],
							"user_id"   => 	$GetUserId,
							"date"      => 	date("Y-m-d"),
							'term_type'=>2
	
					);
					$this->_name='tb_quoatation_termcondition';
					$this->insert($data_item);
				}
			}
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getSaleorderItemById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM $this->_name WHERE id = $id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getSaleorderItemDetailid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_boqdetail` WHERE saleorder_id=$id ";
		return $db->fetchAll($sql);
	}
	function getTermconditionByid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_quoatation_termcondition` WHERE quoation_id=$id AND term_type=2 ";
		return $db->fetchAll($sql);
	} 
}