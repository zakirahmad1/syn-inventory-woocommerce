<?php
defined('BASEPATH') or exit('No direct script access allowed');
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


//use \Libraries\REST_Controller;

require APPPATH . 'libraries/RestController.php';
require APPPATH . 'libraries/Format.php';

class V1 extends RestController
{

    public function __construct()
    {
        parent::__construct();
        //$this->load->library('form_validation','sma');
        $this->load->model('api');
		$this->lang->admin_load('sales', $this->Settings->user_language);
		
        $this->load->library('form_validation');
        $this->load->admin_model('sales_model');
		
    }

    public function sale_post()
    {
		
		
		//echo "<pre>";print_r($row);exit;
        // Authentication logic here
		//echo "<pre>";print_r($_POST);exit;
			$customer_details = $this->api->getCompanyByID($this->input->post('email_address'));
			
			if(!isset($customer_details->id)){
				$customer_details = new stdClass();
				$customer_details->id= $this->api->insertCustomer($_POST);
				
				
			}else{
				//echo "customer i ready inserted";
			}
			//echo $customer_details->id;exit;
			//echo "<pre>";print_r($customer_details);
        // Validate input data
        $this->form_validation->set_rules('site', 'site', 'required');
        // Add more validation rules as needed

        if ($this->form_validation->run() == false) {
            $this->response([
                'status' => false,
                'error' => $this->form_validation->error_array()
            ], RESTController::HTTP_BAD_REQUEST);
        } else {
			$date = date('Y-m-d H:i:s');
			
			$url = $_POST['hostname'];
			$parsed_url = parse_url($url);
			$host_parts = explode('.', $parsed_url['host']);
			$subdomain = $host_parts[0];
			
			$saless=$this->api->getInvoiceByID($subdomain." Order #".$_POST['order_id']);
			if(isset($saless->id)){
				$this->response([
                'status' => false,
                'error' => "Sale already added"
            ], RESTController::HTTP_BAD_REQUEST);
			exit;
			}
			$data=array('date'=>$_POST['order_payment'],
			
						'reference_no'=>$subdomain." Order #".$_POST['order_id'],
						'customer_id'=>$customer_details->id,
						
						'customer'=>$_POST['first'],
						'biller_id'=>3,
						'biller'=>'SmartBrands Pakistan',
						'warehouse_id'=>1,
						'note'=>'',
						'staff_note'=>'',
						'product_discount'=>0,
						'order_discount_id'=>$_POST['additional_info'],
						'order_discount'=>$_POST['additional_info'],
						'total_discount'=>$_POST['additional_info'],
						'product_tax'=>0,
						'order_tax_id'=>1,
						'order_tax'=>0,
						'total_tax'=>0,
						'shipping'=>0,
						'grand_total'=>($_POST['total_price']-$_POST['additional_info']),
						'total_items'=>$_POST['total_quantity'],
						'sale_status'=>'completed',
						'payment_status'=>'paid',
						'payment_term'=>'',
						'due_date'=>'',
						'paid'=>'paid',
						'created_by'=>2,
						'hash' => hash('sha256', microtime() . mt_rand())
			);
			$products=array();
			foreach($_POST['products'] as $pro){
				$product_by = $this->api->getProductByCODE($pro['sku']);
				//echo $product_by->id;
				//echo $product_by->code;
				//echo "<pre>";print_r($product_by);exit;
				
				$product = array(
                        'product_id' => $product_by->id,//it product store
                        'product_code' => $product_by->code,//product code
                        'product_name' => $pro['name'],
                        'product_type' => 'standard',
                        'option_id' => '',
                        'net_unit_price' => $this->sma->formatDecimal($pro['unit_price']),
                        'unit_price' => $this->sma->formatDecimal($pro['unit_price']),
                        'quantity' => $pro['quantity'],
                        'product_unit_id' =>  1,
                        'product_unit_code' =>1,
                        'unit_quantity' => $pro['quantity'],
                        'warehouse_id' => 1,
                        'item_tax' => 0,
                        'tax_rate_id' => "",
                        'tax' => "",
                        'discount' => "",
                        'item_discount' => 0,
                        'subtotal' => $this->sma->formatDecimal($pro['price']),
                        'serial_no' => '',
                        'real_unit_price' => $this->sma->formatDecimal($pro['unit_price']),
                    );
					$products[]=$product;
			
			
			}
			
			
			
            // Process the sale
            //$data = $this->post();
            // Additional processing if needed
			
	$payment=array('date'=>$_POST['order_payment'],
			'reference_no'=>$_POST['tracking_number'],
			'amount'=>($_POST['total_price']-$_POST['additional_info']),
			'paid_by'=>'cash',
			'cheque_no'=>'',
			'cc_no'=>'',
			'cc_holder'=>"",
			'cc_month'=>"",
			'cc_holder'=>"",
			'cc_year'=>"",
			'cc_type'=>'Visa',
			'created_by'=>2,
			'note'=>"",
			'type'=>'received',
			);
			
		
            if ($this->sales_model->addSale($data, $products, $payment)) {
                $this->response([
                    'status' => true,
                    'message' => 'Sale added successfully'
                ], RESTController::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Failed to add sale'
                ], RESTController::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}
