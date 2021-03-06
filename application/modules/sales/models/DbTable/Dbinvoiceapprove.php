<?php

class Sales_Model_DbTable_Dbinvoiceapprove extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_invoice";
	function getAllSaleOrder($search){
			$db= $this->getAdapter();
			$sql=" SELECT id,
			(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
			(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=tb_sales_order.customer_id LIMIT 1 ) AS customer_name,
			(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=tb_sales_order.customer_id LIMIT 1 ) AS contact_name,
			(SELECT name FROM `tb_sale_agent` WHERE tb_sale_agent.id =tb_sales_order.saleagent_id  LIMIT 1 ) AS staff_name,
			sale_no,date_sold,approved_date,
			(SELECT symbal FROM `tb_currency` WHERE id= currency_id limit 1) As curr_name,
			all_total,discount_value,net_total,
			(SELECT name_en FROM `tb_view` WHERE type=7 AND key_code=is_approved LIMIT 1),
			(SELECT name_en FROM `tb_view` WHERE type=8 AND key_code=pending_status LIMIT 1),
			(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = user_mod) AS user_name
			FROM `tb_sales_order` WHERE is_toinvocie=1 ";
			
			$from_date =(empty($search['start_date']))? '1': " date_sold >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " date_sold <= '".$search['end_date']." 23:59:59'";
			$where = " AND ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " sale_no LIKE '%{$s_search}%'";
				$s_where[] = " net_total LIKE '%{$s_search}%'";
				$s_where[] = " paid LIKE '%{$s_search}%'";
				$s_where[] = " balance LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch_id']>0){
				$where .= " AND branch_id = ".$search['branch_id'];
			}
			if($search['customer_id']>0){
				$where .= " AND customer_id =".$search['customer_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
	
		return $db->fetchAll($sql.$where.$order);
	}
	function getInvoieExisting($saleid){
		$db = $this->getAdapter();
		$sql="SELECT id,invoice_no FROM `tb_invoice` WHERE sale_id=$saleid limit 1 ";
		return $db->fetchRow($sql);
	}
	public function addInvoiceApproved($data)	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
		if($data['approved_name']==1){	//approval and create invoice	
			$this->_name="tb_boqcost";
				$data_to = array(
					'pending_status'=>4,			
				);
			if(!empty($data['boqcost_id'])){
				$where=" id = ".$data['boqcost_id'];
				$this->update($data_to, $where);
			}
			$this->_name="tb_boq";
			$data_to = array(
						'is_toinvocie'=>1,
						'pending_status'=>5,
						'is_approved'=>$data['approved_name'],
						'approved_note'=>$data['app_remark'],
						'approved_date'=>$data['app_date'],
						'approved_userid'=>$GetUserId,
				);
			$where=" id = ".$data['boqcost_id'];
			$this->update($data_to, $where);
			$dbg = new Application_Model_DbTable_DbGlobal();
			$result = 1;
			
			$sql = "DELETE FROM tb_quoatation_termcondition WHERE quoation_id=".$data['boqcost_id'];
			$db->query($sql);
			$ids=explode(',',$data['identity_term']);
				 if(!empty($data['identity_term'])){
					 foreach ($ids as $i)
					 {
						$data_item= array(
								'quoation_id'	=> $data['boqcost_id'],
								'condition_id'	=> $data['termid_'.$i],
								"user_id"   	=> 	$GetUserId,
								"date"      	=> 	date("Y-m-d"),
								'term_type'		=>	3
								
						);
						$this->_name='tb_quoatation_termcondition';
						$this->insert($data_item);
					 }
				}
		}else{// not approval //update to sale order
				$sql = "DELETE FROM tb_quoatation_termcondition WHERE quoation_id="."'".$data["id"]."'";
				$db->query($sql);
 				$ids=explode(',',$data['identity_term']);
 				 if(!empty($data['identity_term'])){
 					 foreach ($ids as $i)
 					 {
						$data_item= array(
								'quoation_id'	=> $data["id"],
								'condition_id'	=> $data['termid_'.$i],
								"user_id"   	=> 	$GetUserId,
								"date"      	=> 	date("Y-m-d"),
								'term_type'		=>	2
						);
						$this->_name='tb_quoatation_termcondition';
						$this->insert($data_item);
 					 }
 				 }
			}			
			$db->commit();
			return $result;
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getProductSaleById($id){//5
		$db = $this->getAdapter();
		$sql=" SELECT
		s.id,
		s.branch_id,
		s.boq_number,s.boq_date,s.remark,s.approved_note,s.approved_date,s.all_total,
		(SELECT NAME FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT qty_perunit FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS qty_perunit,
		(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=(SELECT measure_id FROM `tb_product` WHERE id= so.pro_id LIMIT 1)) AS measue_name,
		(SELECT unit_label FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS unit_label,
		(SELECT serial_number FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS serial_number,
		(SELECT name_en FROM `tb_view` WHERE TYPE=2 AND key_code=(SELECT model_id FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) LIMIT 1) AS model_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT contact_phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS add_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.user_id LIMIT 1 ) AS user_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.approved_userid LIMIT 1 ) AS approved_by,
		(SELECT name_en FROM `tb_view` WHERE TYPE=7 AND key_code=is_approved LIMIT 1) approval_status,
		(SELECT name_en FROM `tb_view` WHERE TYPE=8 AND key_code=pending_status LIMIT 1) processing,
		so.old_price,
		so.qty_order,so.labour_price,so.materail_price,so.parent_service,
		so.is_service,so.sub_total,s.*
		FROM `tb_boq` AS s,
		`tb_boqdetail` AS so WHERE s.id=so.boq_id
		AND s.status=1 AND s.id = $id ";
		return $db->fetchAll($sql);
	} 
}