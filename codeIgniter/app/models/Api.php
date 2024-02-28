<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Model
{

    public function __construct() {
        parent::__construct();
    }
	
	
	  public function getCompanyByID($email) {
        $q = $this->db->get_where('companies', array('email' => $email), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	 public function getProductByCODE($code) {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	 public function getInvoiceByID($reference_no)
    {
        $q = $this->db->get_where('sales', array('reference_no' => $reference_no), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function insertCustomer($data){
		$clause=array(	'name'=>$data['first']." ". $data['last_name'],
						'company'=>'-',
						'state'=>$data['billing_state'],
						'email'=>$data['email_address'],
						'phone'=>$data['phone_number'],
						'address'=>$data['billing_address_1']." ".$data['billing_address_2'],
						'city'=>$data['billing_city'],
						'group_id'=>3,
						'group_name'=>'customer',
						'customer_group_name'=>'General',
						
		
					);
		
		 $this->db->insert('companies', $clause);
		return $this->db->insert_id();
	}
	
	//........................


}
