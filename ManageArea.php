 <?php
defined('BASEPATH') OR exit('No direct script access allowed');
class ManageArea extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->data['controller_name'] = static::class; 
        $this->data['pagename'] = "Manage Area";
        $this->load->library('../controllers/Common');
        $this->load->model("ManageArea_model");
        Common::adminLogin();
    }
    public function index()
    {
        $this->data['countries'] = $this->ManageArea_model->listData('tbl_country',array('is_delete' => '0', 'is_active' => '1'));
        $this->data['lists']      = $this->ManageArea_model->listData('tbl_area', array(
            'is_delete' => '0'
        ));
        $this->adminlayout('managearea/index', $this->data, array(
            'template/common',
            'admincustomlayout/table',
            'admincustomlayout/bootstrapvalidation',
            'admincustomlayout/switchery',
            'admincustomlayout/sweetalert',
            'admincustomlayout/select2'
        ));
    }
    public function getState()
    {
        $id     = $this->db->escape_str($this->input->post('id'));
        $result = $this->ManageArea_model->listData('tbl_state', array(
            'is_delete' => '0',
            'is_active' => '1',
            'country_id' => $id
        ));
        echo json_encode($result);
    }
    public function getCity()
    {
        $id     = $this->db->escape_str($this->input->post('id'));
        $result = $this->ManageArea_model->listData('tbl_city', array(
            'is_delete' => '0',
            'is_active' => '1',
            'state_id' => $id
        ));
        echo json_encode($result);
    }
    public function addAreaProcess()
    {
        $data      = array(
            'country_id' => $this->db->escape_str($this->input->post('country')),
            'state_id' => $this->db->escape_str($this->input->post('stateId')),
            'city_id' => $this->db->escape_str($this->input->post('cityId')),
            'area_name' => $this->db->escape_str($this->input->post('area')),
            'timestamp' => Common::timestamp()
        );
        $areaId = $this->ManageArea_model->addData('tbl_area', $data);
        if (isset($areaId)) {
            echo json_encode(array(
                'error' => 1,
                'url' => URL. 'ManageArea',
                'displaymessage' => 'Area Added Successfully',
                'messagetype' => SUCCESS
            ));
        }
    }
    public function changeStatus()
    {
        $id            = Hash::decrypt($this->input->post('recordId'));
        $active        = $this->input->post('recordActive');
        $updateData    = ($active == "1") ? "0" : "1";
        $recordDeatils = array(
            'is_active' => $updateData,
            'timestamp' => Common::timestamp()
        );
        $updateState   = $this->ManageArea_model->updateData('tbl_area', $recordDeatils,array('area_id' => $id));
        echo json_encode(array(
            'newvalue' => $updateData,
            'displaymessage' => 'Status Updated Successfully',
            'messagetype' => SUCCESS,
            'url' => URL. 'ManageArea'
        ));
    }
     public function checkArea($areaId = "")
    {
        $area = $this->input->post('area');
        $country = $this->input->post('country');
        $state = $this->input->post('state');
        $city = $this->input->post('city');
        if ($areaId == "") {
            $result = $this->ManageArea_model->listData('tbl_area', array('area_name' => $area,
                'city_id' => $city,
                'state_id' => $state,
                'country_id' => $country,
                'is_delete' => '0'
                ));
        } else {
            $areaArray = array(
                'is_delete' => '0',
                'area_id !=' => HASH::decrypt($areaId),
                'city_id' => $city,
                'state_id' => $state,
                'country_id' => $country,
                'area_name' => $area
            );
            $result       = $this->ManageArea_model->whereArray('tbl_area', $areaArray);
        }
        $checkArea = (count($result) > 0) ? "1" : "0";
        if ($checkArea) {
            $isAvailable = false;
        } else {
            $isAvailable = true;
        }
        echo json_encode(array(
            'valid' => $isAvailable
        ));
    }
    public function deleteArea($areaId)
    {
        $areaId = Hash::decrypt($areaId);
        $data      = array(
            'is_delete' => 1,
            'timestamp' => Common::timestamp()
        );
        $areaId = $this->ManageArea_model->updateData('tbl_area', $data, array('area_id'=> $areaId));
        if ($areaId) {
            $displayMessage = "Area deleted successfully";
            $class          = "alert-success";
            $error          = FALSE;
        } else {
            $displayMessage = "Failed to delete Area";
            $error          = TRUE;
            $class          = "alert-danger";
        }
        echo json_encode(array(
            "message" => $displayMessage,
            "class" => $class,
            "error" => $error,
            "url" =>URL. 'ManageArea'
        ));
    }
    public function viewArea()
    {
        $recordId              = Hash::decrypt($this->input->post('recordId'));
        $this->data['area'] = $this->ManageArea_model->listData('tbl_area',array('area_id' => $recordId));
        $this->data['city'] = $this->ManageArea_model->cityjoin(array("area_id" => $recordId));
        $this->data['state'] = $this->ManageArea_model->statejoin(array("area_id" => $recordId));
        $this->data['country'] = $this->ManageArea_model->countryjoin(array("area_id" => $recordId));
        $viewCity          = $this->load->view('managearea/view', $this->data, true);
        echo json_encode(array(
            'data' => $viewCity,
            'error' => 0,
            'url' => '',
            'displaymessage' => '',
            'messagetype' => ''
        ));
    }
    public function updateArea()
    {
        $recordId              = Hash::decrypt($this->input->post('recordId'));
       $this->data['city'] = $this->ManageArea_model->cityeditjoin(array(
            'tbl_area.area_id' => $recordId,
            "tbl_city.is_delete" => '0',
            "tbl_city.is_active" => '1'
        ));
       $this->data['area'] =  $this->ManageArea_model->listData('tbl_area', array('area_id' => $recordId));
       $this->data['countries'] = $this->ManageArea_model->listData('tbl_country',array('is_delete' => '0', 'is_active' => '1'));
       $this->data['states'] = $this->ManageArea_model->stateeditjoin(array(
            'tbl_area.area_id' => $recordId,
            "tbl_state.is_delete" => '0',
            "tbl_state.is_active" => '1'
        ));
        $viewArea           = $this->load->view('managearea/edit', $this->data, true);
        echo json_encode(array(
            'data' => $viewArea,
            'error' => 0,
            'url' => '',
            'displaymessage' => '',
            'messagetype' => ''
        ));
    }
    public function updateAreaProcess()
    {
        $areaId = Hash::decrypt($this->input->post('areaId'));
        $data      = array(
           'country_id' => $this->db->escape_str($this->input->post('country')),
           'state_id' => $this->db->escape_str($this->input->post('stateId')),
           'city_id' => $this->db->escape_str($this->input->post('cityId')),
           'area_name' => $this->db->escape_str($this->input->post('area')),
           'timestamp' => Common::timestamp()
        );
        $areaId = $this->ManageArea_model->updateData('tbl_area', $data, array('area_id'=> $areaId));
        if (isset($areaId)) {
            echo json_encode(array(
                'error' => 1,
                'url' => URL. 'ManageArea',
                'displaymessage' => 'Area Updated Successfully',
                'messagetype' => SUCCESS
            ));
        }
    }
}