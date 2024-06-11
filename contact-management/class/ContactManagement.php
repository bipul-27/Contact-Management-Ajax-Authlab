<?php

class ContactManagement{

    private $message = "";
    private $status = "";
    private $action= "";
    public function __construct()
    {
        add_action("admin_menu",array($this, 'addAdminMenus'));
        add_action("admin_enqueue_scripts",array($this, 'addEnqueueScript'));
        add_action("wp_ajax_sms_ajax_handler", array($this,"sms_ajax_handler"));
    }

    public function addAdminMenus()
    {
        add_menu_page( 'Contact Management', "Contact Management", "manage_options", "contact-management",array($this,'listContactCallback'), "dashicons-admin-home" );

        add_submenu_page( "contact-management", "Contact List", "List", "manage_options", "contact-management", array($this,'listContactCallback'));

        add_submenu_page( "contact-management", "Add Contact", "Add Contact", "manage_options", "add-contact", array($this,'addContactCallback') );
    }

    public function sms_ajax_handler() {
    global $wpdb;
    $table_prefix = $wpdb->prefix;
    if (isset($_REQUEST['param']) && $_REQUEST['param'] == "save_form") {
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_text_field($_POST['email']);
        $gender = sanitize_text_field($_POST['gender']);
        $phone = sanitize_text_field($_POST['phone']);
        $operation_type = sanitize_text_field($_POST['operation_type']);

        if($operation_type=="edit")
        {
            $contact_id= $_REQUEST['contact_id'];
            $wpdb->update("{$table_prefix}contact_system",array(
                "name"=>$name,
                "email"=>$email,
                
                "gender"=>$gender,
                "phone_no"=>$phone,
            ),array(
                "id"=>$contact_id
            ));

            echo json_encode(array(
                "status"=>1,
                "message"=>"Contact updated successfully",
                "data"=>[]
            ));
        }
        elseif($operation_type=="add")
        {
            $wpdb->insert("{$table_prefix}contact_system", array(
                "name" => $name,
                "email" => $email,
                "gender" => $gender,
                "phone_no" => $phone
            ));
    
            $contact_id = $wpdb->insert_id;
            if ($contact_id > 0) {
                echo json_encode(array(
                    "status" => 1,
                    "message" => "Contact added successfully",
                    "data" => []
                ));
            } else {
                echo json_encode(array(
                    "status" => 0,
                    "message" => "Failed to add contact",
                    "data" => []
                ));
            }
        }

        
    }
    elseif(isset($_REQUEST['param']) && $_REQUEST['param'] == "load_contact")
    {
        $contacts = $wpdb->get_results("SELECT * FROM {$table_prefix}contact_system",ARRAY_A);

        if(count($contacts)>0)
        {
            echo json_encode([
                "status"=>1,
                "message"=>"Contact Data",
                "data"=>$contacts
            ]);
        }
        else
        {
            echo json_encode([
                "status"=>0,
                "message"=>"Failed",
                "data"=>[]
            ]);
        }

    }
    wp_die();
}


public function listContactCallback()
{
    if (isset($_GET['action']) && $_GET['action'] == "edit") {
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        $this->action = "edit";
        $contact_id = intval($_GET['id']);
        if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['btn-submit'])) {
            $name = sanitize_text_field($_POST['name']);
            $email = sanitize_email($_POST['email']);
            $gender = sanitize_text_field($_POST['gender']);
            $phone = sanitize_text_field($_POST['phone']);
            
            $updated = $wpdb->update(
                "{$table_prefix}contact_system",
                array(
                    "name" => $name,
                    "email" => $email,
                    "gender" => $gender,
                    "phone_no" => $phone
                ),
                array("id" => $contact_id)
            );
            
            if ($updated !== false) {
                $this->message = "Contact updated successfully.";
                $this->status = 1;
            } else {
                $this->message = "Failed to update contact.";
                $this->status = 0;
            }
        }
        $contacts = $this->getContactData($contact_id);
        $action = $this->action;
        $displayMessage = $this->message;
        include_once CMS_PLUGIN_PATH . "pages/add-contact.php";
    } 
    elseif(isset($_GET['action']) && $_GET['action'] == "view"){
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        $this->action = "view";
        $contact_id = intval($_GET['id']);
        $contacts = $this->getContactData($contact_id);
        $action = $this->action;

        include_once CMS_PLUGIN_PATH . "pages/add-contact.php";

    }
    else {
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        if(isset($_GET['action']) && $_GET['action'] == "delete")
        {
            $wpdb->delete("{$table_prefix}contact_system",array(
                "id"=>intval($_GET['id'])
            ));
            $this->message="Data Deleted Successfully";
            $displayMessage = $this->message;
        }
        $contacts = $wpdb->get_results("SELECT * FROM {$table_prefix}contact_system", ARRAY_A);
        include_once CMS_PLUGIN_PATH . "pages/list-contact.php";   
    }
}
// public function listContactCallback()
// {
//     global $wpdb;
//     $table_prefix = $wpdb->prefix;

//     if (isset($_GET['action']) && $_GET['action'] == "edit") {
//         $this->action = "edit";
//         $contact_id = intval($_GET['id']);
//         if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['btn-submit'])) {
//             $name = sanitize_text_field($_POST['name']);
//             $email = sanitize_email($_POST['email']);
//             $gender = sanitize_text_field($_POST['gender']);
//             $phone = sanitize_text_field($_POST['phone']);

//             $updated = $wpdb->update(
//                 "{$table_prefix}contact_system",
//                 array(
//                     "name" => $name,
//                     "email" => $email,
//                     "gender" => $gender,
//                     "phone_no" => $phone
//                 ),
//                 array("id" => $contact_id)
//             );

//             if ($updated !== false) {
//                 $this->message = "Contact updated successfully.";
//                 $this->status = 1;
//             } else {
//                 $this->message = "Failed to update contact.";
//                 $this->status = 0;
//             }
//         }
//         $contacts = $this->getContactData($contact_id);
//         $action = $this->action;
//         $displayMessage = $this->message;

        
        
//         include_once CMS_PLUGIN_PATH . "pages/add-contact.php";
//     } elseif (isset($_GET['action']) && $_GET['action'] == "view") {
//         $this->action = "view";
//         $contact_id = intval($_GET['id']);
//         $contacts = $this->getContactData($contact_id);
//         $action = $this->action;

        
//         include_once CMS_PLUGIN_PATH . "pages/add-contact.php";
//     } else {
//         if (isset($_GET['action']) && $_GET['action'] == "delete") {
//             $wpdb->delete("{$table_prefix}contact_system", array(
//                 "id" => intval($_GET['id'])
//             ));
//             $this->message = "Data Deleted Successfully";
//             $displayMessage = $this->message;
//         }
//         $contacts = $wpdb->get_results("SELECT * FROM {$table_prefix}contact_system", ARRAY_A);
//         include_once CMS_PLUGIN_PATH . "pages/list-contact.php";
//     }
// }


        
    
    
    private function getContactData($contact_id)
    {
        global $wpdb;
        $table_prefix=$wpdb->prefix;
        $contacts= $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_prefix}contact_system WHERE id=%d",$contact_id), ARRAY_A
        );
        return $contacts;
    }

    public function addContactCallback()
    {
        if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['btn-submit']))
        {
           
            $this->saveContactData();

        }
        $displayMessage= $this->message;
        $displayStatus= $this->status;
        include_once CMS_PLUGIN_PATH ."pages/add-contact.php";
    }

    private function saveContactData()
    {
        global $wpdb;
            $name=sanitize_text_field( $_POST['name'] );
            $email=sanitize_text_field( $_POST['email'] );
            $gender=sanitize_text_field( $_POST['gender'] );
            $phone=sanitize_text_field( $_POST['phone'] );

            $table_prefix=$wpdb->prefix;

            $wpdb->insert("{$table_prefix}contact_system",array(
                'name'=>$name,
                'email'=>$email,
                'gender'=>$gender,
                'phone_no'=>$phone
            ));
            $contact_id=$wpdb->insert_id;

            if($contact_id>0)
            {
                $this->message="Contact added successfully";
                $this->status=1;
            }
            else
            {
                $this->message="Failed to add contact";
                $this->status=0;
            }
    }

    public function createContactTable()
    {
        global $wpdb;
        $prefix=$wpdb->prefix;
        $sql= '
   CREATE TABLE `'.$prefix.'contact_system` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `email` varchar(80) DEFAULT NULL,
  `gender` enum("male","female","other") DEFAULT NULL,
  `phone_no` varchar(25) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;';

        include_once ABSPATH. "wp-admin/includes/upgrade.php";
        dbDelta($sql);
    }
    public function dropTable()
    {
        global $wpdb;
        $prefix=$wpdb->prefix;

        $sql="DROP TABLE IF EXISTS ".$prefix."contact_system";
        $wpdb->query($sql);
    }

    public function addEnqueueScript()
    {
        wp_enqueue_style( "datatable-css", CMS_PLUGIN_URL ."assets/css/dataTables.dataTables.min.css", array(), "1.0", "all" );
        wp_enqueue_style("custom-css", CMS_PLUGIN_URL ."assets/css/custom.css", array(),"1.0","all");
        wp_enqueue_style("toastr-css", CMS_PLUGIN_URL ."assets/css/toastr.min.css", array(),"1.0","all");


        wp_enqueue_script( "datatable-js", CMS_PLUGIN_URL ."assets/js/dataTables.min.js", array("jquery"), "1.0", true );
        wp_enqueue_script("script-js", CMS_PLUGIN_URL ."assets/js/script.js",array("jquery"),"1.0", true);
        wp_enqueue_script("toastr-js", CMS_PLUGIN_URL ."assets/js/toastr.min.js",array("jquery"),"1.0", true);

        $data="var sms_ajax_url='".admin_url('admin-ajax.php')."'";
        wp_add_inline_script( "script-js", $data );
    }
}