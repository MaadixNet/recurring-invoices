<?php
class ModelSaleInvoice extends Model {
	public function deleteInvoice($invoice_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "cycling_invoices` WHERE  = invoice_id'" . (int)$invoice_id . "'");
	}	
	
	public function getInvoice($invoice_id) {
		$invoice_query = $this->db->query("SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = o.customer_id) AS customer FROM `" . DB_PREFIX . "cycling_invoices` o WHERE o.invoice_id = '" . (int)$invoice_id . "'");

		if ($invoice_query->num_rows) {
                        $order_id=$invoice_query->row['order_id'];

			$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

			foreach ($order_product_query->rows as $product) {
				$reward= $product['reward'];
			}
			


			$language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			return array(
				'invoice_id'                => $invoice_query->row['invoice_id'],
				'invoiceNumber'             => $invoice_query->row['invoiceNumber'],
				'customer_id'             => $invoice_query->row['customer_id'],
				'customer'                => $invoice_query->row['customer'],
				'firstname'               => $customer_query->row['firstname'],
				'lastname'                => $customer_query->row['lastname'],
				'email'                   => $customer_query->row['email'],
				'telephone'               => $customer_query->row['telephone'],
				'fax'                     => $customer_query->row['fax'],
				'custom_field'            => json_decode($order_query->row['custom_field'], true),
				'payment_method'          => $order_query->row['payment_method'],
				'payment_code'            => $order_query->row['payment_code'],

				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'order_status_id'         => $order_query->row['order_status_id'],
				'order_status'            => $order_query->row['order_status'],

				'commission'              => $order_query->row['commission'],
				'language_id'             => $order_query->row['language_id'],
				'language_code'           => $language_code,
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'ip'                      => $order_query->row['ip'],
				'forwarded_ip'            => $order_query->row['forwarded_ip'],
				'user_agent'              => $order_query->row['user_agent'],
				'accept_language'         => $order_query->row['accept_language'],
				'date_added'              => $order_query->row['date_added'],
				'date_modified'           => $order_query->row['date_modified']
			);
		} else {
			return;
		}
	}

	public function getInvoices($data = array()) {
		$sql = "SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = o.customer_id) AS customer, o.invoice_id, o.invoiceNumber, o.customer_id, o.status_id, o.amount,o.order_id, o.date_added, o.datePayed,o.dateExpire, o.factPeriod FROM `" . DB_PREFIX . "cycling_invoices` o";

		if (isset($data['filter_invoice_status'])) {
			$implode = array();

			$invoice_statuses = explode(',', $data['filter_invoice_status']);

			foreach ($invoice_statuses as $invoice_status_id) {
				$implode[] = "o.status_id = '" . (int)$invoice_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} else {
			$sql .= " WHERE o.status_id > '0'";
		}

		if (!empty($data['filter_invoice_id'])) {
			$sql .= " AND o.invoice_id = '" . (int)$data['filter_invoice_id'] . "'";
		}

		if (!empty($data['filter_customer_id'])) {
			$sql .= " AND o.customer_id = '" . (int)$data['filter_customer_id'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(o.datePayed) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND o. amount= '" . (float)$data['filter_total'] . "'";
		}

		$sort_data = array(
			'o.invoice_id',
			'customer',
			'invoice_status',
			'o.date_added',
			'o.datePayed',
			'o.amount'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY o.invoice_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}



        public function getInvoiceStatuses($data = array()) {
                if ($data) {
                        $sql = "SELECT * FROM " . DB_PREFIX . "cycling_invoices_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'";

                        $sql .= " ORDER BY name";

                        if (isset($data['order']) && ($data['order'] == 'DESC')) {
                                $sql .= " DESC";
                        } else {
                                $sql .= " ASC";
                        }

                        if (isset($data['start']) || isset($data['limit'])) {
                                if ($data['start'] < 0) {
                                        $data['start'] = 0;
                                }

                                if ($data['limit'] < 1) {
                                        $data['limit'] = 20;
                                }

                                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
                        }

                        $query = $this->db->query($sql);

                        return $query->rows;
                } else {
                        $invoice_status_data = $this->cache->get('invoice_status.' . (int)$this->config->get('config_language_id'));

                        if (!$invoice_status_data) {
                                $query = $this->db->query("SELECT invoice_status_id, name FROM " . DB_PREFIX . "cycling_invoices_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY name");

                                $invoice_status_data = $query->rows;

                                $this->cache->set('invoice_status.' . (int)$this->config->get('config_language_id'), $invoice_status_data);
                        }

                        return $invoice_status_data;
                }
        }

        /*        
         *Functions for cron Invoices 
         *
         */
          public function getExpiringPayment($interval){
            //Calculate ecpirind date for each interval in $interval array
            //change $interval array values from number of days to a date from today
            foreach ($interval as &$item){
                  $sub_period = " +" .$item . " days";
                  $expiration_date = strtotime($sub_period, strtotime(date ( 'Y-m-d')));
                  $item = date ( 'Y-m-d' , $expiration_date );
            }
            $days = join("','",$interval);
            $sql = "SELECT * FROM " . DB_PREFIX . "cycling_payments WHERE expiringDate IN ('$days')";
            /*
            $numItems = count($interval);
            $i = 0;
            $sql = "SELECT * FROM " . DB_PREFIX . "cycling_payments WHERE";
            //$intervals = implode (',' , $daysbefore);  
            foreach ($intervals as $interval){
              $sql .= " expiringDate = DATE(now() + INTERVAL ". $interval ." DAY";
              if(++$i < $numItems) {
                  $sql .= " OR";;
              }
            $sql .= " )";
            */
              //$dbquery = $this->db->query("SELECT * FROM " . DB_PREFIX . "cycling_payments WHERE expiringDate = DATE(now() + INTERVAL ". $daysbefore ." DAY)");
              $dbquery = $this->db->query($sql);
                return $dbquery->rows;
    
            }

        public function getMonthsPayed($order_id,$order_product_id){
            $cycling_query= $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE  (name= 'Cycling Payment' OR name ='Ciclo de pago') AND order_product_id = '" . (int)$order_product_id . "' AND order_id = '" . (int)$order_id . "'");
            $cycling = (isset($cycling_query->row['product_option_value_id']))?$cycling_query->row['product_option_value_id']:'0';
            if ($cycling){
                //232 is the option value for cycling payment
                switch ($cycling){
                case 31:
                case 33:
                case 47:
                case 55:
                  $months= 3;
                  break;
                case 32:
                case 34:
                case 48:
                case 56:
                  $months= 12;
                  break;
                default:
                  $months = 1;
                  break;
                }
                return $months;
            }

        }

          public function checkExistingCycleInvoice($data) {

            /*
                 $data['cycliing_id'];
                 $datai['customer_id']
                 $data['expiringDate']
                 $data['product_id'];
                 $data['fact_rediod']
                  $data['next_expiration_date']

            
            */
                $dbquery = $this->db->query("SELECT * FROM " . DB_PREFIX . "cycling_invoices WHERE customer_id = '" . (int)$data['customer_id'] . "' AND cycling_id = '" . (int)$data['cycling_id']  . "' AND dateExpire= '". $data['date_expire'] . "' AND factPeriod = '" . $data['Factperiod'] . "'");
                return $dbquery->rows;
          
            }

        public function getOrderProductById($data) {
            $query= $this->db->query("SELECT * FROM  `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$data['order_id'] . "' AND order_product_id = '" . (int)$data['order_product_id'] . "'");
            return $query->rows;
        }

        
	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

       public  function getnextInvNumber()
      {

        $inv_query= $this->db->query("SELECT * FROM  `" . DB_PREFIX . "cycling_invoices` ORDER BY invoice_id DESC LIMIT 1");
        $last_inv_no = $inv_query->row['invoiceNumber'];
        $parts=explode("-", $last_inv_no);
        $lastyeainv=$parts[0];
        $invnum=$parts[1];
        if((!$last_inv_no) || (date('Y') != $lastyeainv))$invnum=0;
        $inv_num=date('Y') .'-'. ($invnum+1);
        return $inv_num;
      }

        public function addInvoice($data) {

                $cycling_id = (isset($data ['cycling_id']))? $data ['cycling_id']:'0';
                $netxinvno=$this->getnextInvNumber();
                $this->db->query("INSERT INTO `" . DB_PREFIX . "cycling_invoices` SET invoiceNumber = '" . $netxinvno  . "', cycling_id = '" . (int)$cycling_id . "',  customer_id = '" . (int)$data['customer_id'] . "', txnid = '" . $this->db->escape($data['txnid']) . "', status_id = '" . (int)$data['status_id'] . "', amount = '" . $this->db->escape($data['amount']) . "',order_id = '" . (int)$data['order_id'] . "', date_added = '" . $data ['date_added'] . "', datePayed = '" . $data ['date_payed'] . "', dateExpire = '". $data ['date_expire'] . "', factPeriod = '" . $data ['Factperiod'] . "'");

                $invoice_id = $this->db->getLastId();

                return $invoice_id;
        }


	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}

	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderVoucherByVoucherId($voucher_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE voucher_id = '" . (int)$voucher_id . "'");

		return $query->row;
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function getTotalInvoices($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "cycling_invoices`";

		if (isset($data['filter_invoice_status'])) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_invoice_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} else {
			$sql .= " WHERE status_id > '0'";
		}

		if (!empty($data['filter_invoice_id'])) {
			$sql .= " AND invoice_id = '" . (int)$data['filter_invoice_id'] . "'";
		}

		if (!empty($data['filter_customer_id'])) {
			$sql .= " AND customer_id = '" . (int)$data['filter_customer_id'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND amount = '" . (float)$data['filter_total'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalInvoicesByStoreId($store_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "cycling_invoices`");

		return $query->row['total'];
	}

	public function getTotalOrdersByOrderStatusId($invoice_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "cycling_invoices` WHERE invoice_status_id = '" . (int)$invoice_status_id . "' AND status_id > '0'");

		return $query->row['total'];
	}

	public function getTotalOrdersByProcessingStatus() {
		$implode = array();

		$order_statuses = $this->config->get('config_processing_status');

		foreach ($order_statuses as $order_status_id) {
			$implode[] = "order_status_id = '" . (int)$order_status_id . "'";
		}

		if ($implode) {
			$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE " . implode(" OR ", $implode));

			return $query->row['total'];
		} else {
			return 0;
		}
	}
/*
	public function getTotalOrdersByCompleteStatus() {
		$implode = array();

		$order_statuses = $this->config->get('config_complete_status');

		foreach ($order_statuses as $order_status_id) {
			$implode[] = "order_status_id = '" . (int)$order_status_id . "'";
		}

		if ($implode) {
			$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE " . implode(" OR ", $implode) . "");

			return $query->row['total'];
		} else {
			return 0;
		}
	}

	public function getTotalOrdersByLanguageId($language_id) {
              $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE language_id = '" . (int)$language_id . "' AND order_status_id > '0'");

		return $query->row['total'];
	}

	public function getTotalOrdersByCurrencyId($currency_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE currency_id = '" . (int)$currency_id . "' AND order_status_id > '0'");

		return $query->row['total'];
	}

	public function createInvoiceNo($order_id) {
		$order_info = $this->getOrder($order_id);

		if ($order_info && !$order_info['invoice_no']) {
			$query = $this->db->query("SELECT MAX(invoice_no) AS invoice_no FROM `" . DB_PREFIX . "order` WHERE invoice_prefix = '" . $this->db->escape($order_info['invoice_prefix']) . "'");

			if ($query->row['invoice_no']) {
				$invoice_no = $query->row['invoice_no'] + 1;
			} else {
				$invoice_no = 1;
			}

			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET invoice_no = '" . (int)$invoice_no . "', invoice_prefix = '" . $this->db->escape($order_info['invoice_prefix']) . "' WHERE order_id = '" . (int)$order_id . "'");

			return $order_info['invoice_prefix'] . $invoice_no;
		}
	}

	public function getOrderHistories($order_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT oh.date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added ASC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalOrderHistories($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

	public function getTotalOrderHistoriesByOrderStatusId($order_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_history WHERE order_status_id = '" . (int)$order_status_id . "'");

		return $query->row['total'];
	}

	public function getEmailsByProductsOrdered($products, $start, $end) {
		$implode = array();

		foreach ($products as $product_id) {
			$implode[] = "op.product_id = '" . (int)$product_id . "'";
		}

		$query = $this->db->query("SELECT DISTINCT email FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_product op ON (o.order_id = op.order_id) WHERE (" . implode(" OR ", $implode) . ") AND o.order_status_id <> '0' LIMIT " . (int)$start . "," . (int)$end);

		return $query->rows;
	}

	public function getTotalEmailsByProductsOrdered($products) {
		$implode = array();

		foreach ($products as $product_id) {
			$implode[] = "op.product_id = '" . (int)$product_id . "'";
		}

		$query = $this->db->query("SELECT DISTINCT email FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_product op ON (o.order_id = op.order_id) WHERE (" . implode(" OR ", $implode) . ") AND o.order_status_id <> '0'");

		return $query->row['email'];
        }
 */
}
