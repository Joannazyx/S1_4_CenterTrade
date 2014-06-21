<?php

class Pending_model extends CI_Model {
	
	public function __construct()
	{
		$this->load->database();
	}

	/*@author KHC @version 1.0
	 * @parameter $data:待插入数据
	 * @return 布尔值*/
	public function add_sell_pending($data)
	{
		$this->db->trans_start();
		$this->db->insert('pending_sell_table', $data); 
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	/*@author KHC @version 1.0
	 * @parameter $data:待插入数据
	 * @return 布尔值*/
	public function add_buy_pending($data)
	{
		$this->db->trans_start();
		$this->db->insert('pending_buy_table', $data);
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/*@author KHC @version 1.0
	 * @parameter $commissionID:订单号
	 * @return 返回订单号对应的买卖操作*/
	public function get_type($commissionID)
	{
		$this->db->select('CommissionType');
		$query = $this->db->get_where('pending_buy_table', array('CommissionID' => $commissionID));
		if($query->num_rows() > 0)
			return 0;
		else {
			return 1;
		}
	}

	/*@author KHC @version 1.0
	 * @parameter $commissionID:待删除订单号
	 * @return 布尔值*/
	public function withdraw_buy_pending($commissionID)
	{
		$this->db->trans_start();
		$query = $this->db->get_where('pending_buy_table', array('CommissionID' => $commissionID));
		foreach ($query->result_array() as $row) {
			$this->db->insert('withdraw_request_table',$row);
		}
		$this->db->delete('pending_buy_table', array('CommissionID' => $commissionID)); 
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/*@author KHC @version 1.0
	 * @parameter $commissionID:待删除订单号
	 * @return 布尔值*/
	public function withdraw_sell_pending($commissionID)
	{
		$this->db->trans_start();
		$query = $this->db->get_where('pending_sell_table', array('CommissionID' => $commissionID));
		foreach ($query->result_array() as $row) {
			$this->db->insert('withdraw_request_table',$row);
		}
		$this->db->delete('pending_sell_table', array('CommissionID' => $commissionID)); 
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/*@author KHC @version 1.0
	 * @parameter $stockID:待挂起股票号
	 * @return 布尔值*/
	public function suspendPending($stockID)
	{
		$this->db->trans_start();
		$data = array('Suspend' => 1);
		$this->db->where('StockID', $stockID);
		$this->db->update('pending_buy_table', $data);
		$this->db->where('StockID', $stockID);
		$this->db->update('pending_sell_table', $data);
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/*@author KHC @version 1.0
	 * @parameter $stockID:待不挂起股票号
	 * @return 布尔值*/
	public function unsuspendPending($stockID)
	{
		$this->db->trans_start();
		$data = array('Suspend' => FALSE);
		$this->db->where('StockID', $stockID);
		$this->db->update('pending_buy_table', $data);
		$this->db->where('StockID', $stockID);
		$this->db->update('pending_sell_table', $data);
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/*@author KHC @version 1.0
	 * @parameter 无
	 * @return 停盘操作结果布尔值*/
	public function shutdownPending()
	{
		$this->db->trans_start();
		$query = $this->db->get('pending_buy_table');
		foreach ($query->result_array() as $row) {
			$this->db->insert('withdraw_request_table',$row);
		}
		$this->db->empty_table('pending_buy_table'); 
		$query = $this->db->get('pending_sell_table');
		foreach ($query->result_array() as $row) {
			$this->db->insert('withdraw_request_table',$row);
		}
		$this->db->empty_table('pending_sell_table'); 
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/*@author KHC @version 1.0
	 * @parameter $stockID:待查询股票ID, $order：升序还是降序
	 * @return 在待处理买指令列表对交易价格排序后的查询结果*/
	public function sortcommbuyprice($stockID, $order)
	{
		// if($order == 0)
		// 	$query = mysql_query("SELECT * FROM pending_buy_table WHERE StockID = $stockID ORDER BY CommissionPrice");
	 //    else
		// 	$query = mysql_query("SELECT * FROM pending_buy_table WHERE StockID = $stockID ORDER BY CommissionPrice DESC");	
		// return json_encode($query);
		$sql = "select *  from pending_buy_table where StockID = ? order by CommissionPrice";
		if($order == 1){
			$sql = $sql." desc";
		}
		$res = $this -> db -> query($sql, array($stockID));
		return $res->result_array();
	}

	
	/*@author KHC @version 1.0
	 * @parameter $stockID:待查询股票ID, $order：升序还是降序
	 * @return 在待处理卖指令列表对交易价格排序后的查询结果*/
	public function sortcommsellprice($stockID, $order)
	{
		// if($order == 0)
		// 	$query = mysql_query("SELECT * FROM pending_sell_table WHERE StockID = $stockID ORDER BY CommissionPrice");
	 //    else
		// 	$query = mysql_query("SELECT * FROM pending_sell_table WHERE StockID = $stockID ORDER BY CommissionPrice DESC");	
		// return json_encode($query);

		$sql = "select *  from pending_sell_table where StockID = ? order by CommissionPrice";
		if($order == 1){
			$sql = $sql." desc";
		}
		$res = $this -> db -> query($sql, array($stockID));
		return $res->result_array();
	}


}

?>
