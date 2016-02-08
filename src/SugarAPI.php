<?php

namespace Daryl\SugarAPI;

use Illuminate\Support\Facades\Config;
use nusoap_client;

class SugarAPI
{
    private $apiUrl = '';
    private $username = '';
    private $password = '';
    private $nusoapClient = '';
    private $session_id = '';


    public function __construct()
    {
        $this->apiUrl = Config::get('l5-sugar.url');
        $this->username = Config::get('l5-sugar.username');
        $this->password = Config::get('l5-sugar.password');

        $this->nusoapClient = new nusoap_client($this->apiUrl, true);

        $this->login();
    }

    function login() {
        $login_parameters = array(
            'user_auth' => array(
                'user_name' => $this->username,
                'password' => $this->password,
                'version' => '1'
            ),
            'application_name' => 'iConnect CRM',
            'name_value_list' => array(
            ),
        );

        $login_result = $this->nusoapClient->call('login', $login_parameters);

        if($login_result['id']) {
            $this->session_id = $login_result['id'];

            return true;
        } else {
            return false;
        }

    }

    function get_records($module, $query, $result_fields = array(), $order_by = '', $link_name_to_fields_array = array()) {
        $get_entry_list_parameters = array(
            //session id
            'session' => $this->session_id,
            //The name of the module from which to retrieve records
            'module_name' => $module,
            //The SQL WHERE clause without the word "where".
            'query' => $query,
            //The SQL ORDER BY clause without the phrase "order by".
            'order_by' => $order_by,
            //The record offset from which to start.
            'offset' => 0,
            //A list of fields to include in the results.
            'select_fields' => $result_fields,
            //A list of link names and the fields to be returned for each link name.
            'link_name_to_fields_array' => $link_name_to_fields_array,
            //The maximum number of results to return.
            'max_results' => 2,
            //If deleted records should be included in results.
            'deleted' => 0,
            //If only records marked as favorites should be returned.
            'favorites' => false,
        );

        $result = $this->nusoapClient->call('get_entry_list', $get_entry_list_parameters);

        return $result;
    }

    function count_records($module, $where) {
        $get_entries_count_parameters = array(
            //Session id
            'session' => $this->session_id,
            //The name of the module from which to retrieve records
            'module_name' => $module,
            //The SQL WHERE clause without the word "where".
            'query' => $where,
            //If deleted records should be included in results.
            'deleted' => false
        );

        $result = $this->nusoapClient->call('get_entries_count', $get_entries_count_parameters);

        return $result;
    }

    function insert_record($module, $name_value_list) {

        $set_entry_parameters = array(
            //session id
            'session' => $this->session_id,
            //The name of the module from which to retrieve records.
            'module_name' => $module,
            //Record attributes
            'name_value_list' => $name_value_list,
        );

        $result = $this->nusoapClient->call('set_entry', $set_entry_parameters);

        return $result;

    }

    function set_relationship($module, $module_id, $related_ids, $name_value_list) {
        $set_relationship_parameters = array(
            //session id
            'session' => $this->session_id,
            //The name of the module.
            'module_name' => $module,
            //The ID of the specified module bean.
            'module_id' => $module_id,
            //The relationship name of the linked field from which to relate records.
            'link_field_name' => 'contacts',
            //The list of record ids to relate
            'related_ids' => $related_ids,
            //Sets the value for relationship based fields
            'name_value_list' => $name_value_list,
            //Whether or not to delete the relationship. 0:create, 1:delete
            'delete'=> 0,
        );

        $result = $this->nusoapClient->call('set_relationship', $set_relationship_parameters);

        return $result;
    }

    function get_available_modules($filter = 'default') {
        $get_available_modules_parameters = array(
            //Session id
            "session" => $this->session_id,
            //Module filter. Possible values are 'default', 'mobile', 'all'.
            "filter" => $filter,
        );

        $result = $this->nusoapClient->call('get_available_modules', $get_available_modules_parameters);

        return $result;
    }

    function get_module_fields($module) {
        $get_module_fields_params = array(
            //Session id
            'session' => $this->session_id,
            //The name of the module from which to retrieve records
            'module_name' => $module,
        );

        $result = $this->nusoapClient->call('get_module_fields', $get_module_fields_params);

        return $result;
    }

    function getValueFromResult($field, $result) {
        if(array_key_exists('entry_list', $result)) {
            $entry_list = $result['entry_list'];
            if(!empty($entry_list)) {
                $entry = $entry_list[0];

                foreach ($entry['name_value_list'] as $item) {
                    if($item['name'] == $field) {
                        return $item['value'];
                    }
                }
            }
        }

        return false;

    }

    /**
     * Friendly welcome
     *
     * @param string $phrase Phrase to return
     *
     * @return string Returns the phrase passed in
     */
    public function echoPhrase($phrase)
    {
        return $phrase;
    }
}
