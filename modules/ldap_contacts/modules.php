<?php

/**
 * LDAP contact modules
 * @package modules
 * @subpackage ldap_contacts
 */

if (!defined('DEBUG_MODE')) { die(); }

require APP_PATH.'modules/ldap_contacts/hm-ldap-contacts.php';

/**
 * @subpackage ldap_contacts/functions
 */
function fetch_ldap_contacts($config, $contact_store, $session=false) {
    $ldap_config = ldap_config($config);
    if (count($ldap_config) > 0) {
        $ldap = new Hm_LDAP_Contacts($ldap_config);
        if ($ldap->connect()) {
            $contacts = $ldap->fetch();
            if (count($contacts) > 0) {
                $contact_store->import($contacts);
            }
        }
    }
    return $contact_store;
}

/**
 * @subpackage ldap_contacts/functions
 */
function ldap_config($config) {
    $details = array();
    $ini_file = rtrim($config->get('app_data_dir', ''), '/').'/ldap.ini';
    if (is_readable($ini_file)) {
        $settings = parse_ini_file($ini_file);
        if (!empty($settings)) {
            $details = $settings;
        }
    }
    return $details;
}

/**
 * @subpackage ldap_contacts/handler
 */
class Hm_Handler_load_ldap_contacts extends Hm_Handler_Module {
    public function process() {
        $contacts = $this->get('contact_store');
        $contacts = fetch_ldap_contacts($this->config, $contacts);
        $this->append('contact_sources', 'ldap');
        $this->append('contact_edit', 'ldap');
        $this->out('contact_store', $contacts, false);
    }
}
/**
 * @subpackage ldap_contacts/handler
 */
class Hm_Handler_load_edit_ldap_contact extends Hm_Handler_Module {
    public function process() {
        if (array_key_exists('contact_source', $this->request->get) && $this->request->get['contact_source'] == 'ldap'
            && array_key_exists('contact_id', $this->request->get)) {
            $contacts = $this->get('contact_store');
            $contact = $contacts->get($this->request->get['contact_id']);
            if (is_object($contact)) {
                $current = $contact->export();
                $current['id'] = $this->request->get['contact_id'];
                $this->out('current_ldap_contact', $current);
            }
        }
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_contact_form_end extends Hm_Output_Module {
    protected function output() {
        return '</div></form></div>';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_first_name extends Hm_Output_Module {
    protected function output() {
        $name = get_ldap_value('givenname', $this);
        return '<label class="screen_reader" for="ldap_first_name">'.$this->trans('First Name').'</label>'.
            '<input required placeholder="'.$this->trans('First Name').'" id="ldap_first_name" type="text" name="ldap_first_name" '.
            'value="'.$this->html_safe($name).'" /> *<br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_submit extends Hm_Output_Module {
    protected function output() {
        $label = 'Add';
        return '<input type="submit" value="'.$this->trans($label).'" />'.
            '<input type="button" class="reset_contact" value="'.$this->trans('Reset').'" />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_last_name extends Hm_Output_Module {
    protected function output() {
        $name = get_ldap_value('sn', $this);
        return '<label class="screen_reader" for="ldap_last_name">'.$this->trans('Last Name').'</label>'.
            '<input required placeholder="'.$this->trans('Last Name').'" id="ldap_last_name" type="text" name="ldap_last_name" '.
            'value="'.$this->html_safe($name).'" /> *<br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_title extends Hm_Output_Module {
    protected function output() {
        $title = get_ldap_value('title', $this);
        return '<label class="screen_reader" for="ldap_title">'.$this->trans('Title').'</label>'.
            '<input placeholder="'.$this->trans('Title').'" id="ldap_title" type="text" name="ldap_title" '.
            'value="'.$this->html_safe($title).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_contact_form_start extends Hm_Output_Module {
    protected function output() {

        $title = $this->trans('Add LDAP Contact');
        $form_class='contact_form';
        if ($this->get('current_ldap_contact')) {
            $title = $this->trans('Update LDAP Contact');
            $form_class = 'contact_update_form';
        }
        return '<div class="add_contact"><form class="add_contact_form" method="POST">'.
            '<div class="server_title">'.$title.
            '<img alt="" class="menu_caret" src="'.Hm_Image_Sources::$chevron.'" width="8" height="8" /></div>'.
            '<div class="'.$form_class.'">'.
            '<input type="hidden" name="contact_source" value="ldap" />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_displayname extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('displayname', $this);
        return '<label class="screen_reader" for="ldap_displayname">'.$this->trans('Display Name').'</label>'.
            '<input placeholder="'.$this->trans('Display Name').'" id="ldap_displayname" type="text" name="ldap_mail" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_mail extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('mail', $this);
        return '<label class="screen_reader" for="ldap_mail">'.$this->trans('E-Mail').'</label>'.
            '<input required placeholder="'.$this->trans('E-Mail').'" id="ldap_mail" type="text" name="ldap_mail" '.
            'value="'.$this->html_safe($val).'" /> *<br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_phone extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('telephonenumber', $this);
        return '<label class="screen_reader" for="ldap_phone">'.$this->trans('Telephone Number').'</label>'.
            '<input placeholder="'.$this->trans('Telephone Number').'" id="ldap_phone" type="text" name="ldap_phone" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_fax extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('facsimiletelephonenumber', $this);
        return '<label class="screen_reader" for="ldap_fax">'.$this->trans('Fax Number').'</label>'.
            '<input placeholder="'.$this->trans('Fax Number').'" id="ldap_fax" type="text" name="ldap_fax" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_mobile extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('mobile', $this);
        return '<label class="screen_reader" for="ldap_mobile">'.$this->trans('Mobile Number').'</label>'.
            '<input placeholder="'.$this->trans('Mobile Number').'" id="ldap_mobile" type="text" name="ldap_mobile" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_room extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('roomnumber', $this);
        return '<label class="screen_reader" for="ldap_room">'.$this->trans('Room Number').'</label>'.
            '<input placeholder="'.$this->trans('Room Number').'" id="ldap_room" type="text" name="ldap_room" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_car extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('carlicense', $this);
        return '<label class="screen_reader" for="ldap_car">'.$this->trans('License Plate Number').'</label>'.
            '<input placeholder="'.$this->trans('License Plate Number').'" id="ldap_car" type="text" name="ldap_car" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_org extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('o', $this);
        return '<label class="screen_reader" for="ldap_org">'.$this->trans('Organization').'</label>'.
            '<input placeholder="'.$this->trans('Organization').'" id="ldap_org" type="text" name="ldap_org" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_org_unit extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('ou', $this);
        return '<label class="screen_reader" for="ldap_org_unit">'.$this->trans('Organization Unit').'</label>'.
            '<input placeholder="'.$this->trans('Organization Unit').'" id="ldap_org_unit" type="text" name="ldap_org_unit" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_org_dpt extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('departmentnumber', $this);
        return '<label class="screen_reader" for="ldap_org_dpt">'.$this->trans('Department Number').'</label>'.
            '<input placeholder="'.$this->trans('Department Number').'" id="ldap_org_dpt" type="text" name="ldap_org_dpt" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_emp_num extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('employeenumber', $this);
        return '<label class="screen_reader" for="ldap_emp_num">'.$this->trans('Employee Number').'</label>'.
            '<input placeholder="'.$this->trans('Employee Number').'" id="ldap_emp_num" type="text" name="ldap_emp_num" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_emp_type extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('employeetype', $this);
        return '<label class="screen_reader" for="ldap_emp_type">'.$this->trans('Employment Type').'</label>'.
            '<input placeholder="'.$this->trans('Employment Type').'" id="ldap_emp_type" type="text" name="ldap_emp_type" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_lang extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('preferredlanguage', $this);
        return '<label class="screen_reader" for="ldap_lang">'.$this->trans('Language').'</label>'.
            '<input placeholder="'.$this->trans('Language').'" id="ldap_lang" type="text" name="ldap_lang" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_uri extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('labeleduri', $this);
        return '<label class="screen_reader" for="ldap_uri">'.$this->trans('Website').'</label>'.
            '<input placeholder="'.$this->trans('Website').'" id="ldap_uri" type="text" name="ldap_uri" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_locality extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('l', $this);
        return '<label class="screen_reader" for="ldap_locality">'.$this->trans('Locality').'</label>'.
            '<input placeholder="'.$this->trans('Locality').'" id="ldap_locality" type="text" name="ldap_locality" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_street extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('street', $this);
        return '<label class="screen_reader" for="ldap_street">'.$this->trans('Street').'</label>'.
            '<input placeholder="'.$this->trans('Street').'" id="ldap_street" type="text" name="ldap_street" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_state extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('st', $this);
        return '<label class="screen_reader" for="ldap_state">'.$this->trans('State').'</label>'.
            '<input placeholder="'.$this->trans('State').'" id="ldap_state" type="text" name="ldap_state" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/output
 */
class Hm_Output_ldap_form_postalcode extends Hm_Output_Module {
    protected function output() {
        $val = get_ldap_value('postalcode', $this);
        return '<label class="screen_reader" for="ldap_postalcode">'.$this->trans('Postal Code').'</label>'.
            '<input placeholder="'.$this->trans('Postal Code').'" id="ldap_postalcode" type="text" name="ldap_postalcode" '.
            'value="'.$this->html_safe($val).'" /><br />';
    }
}

/**
 * @subpackage ldap_contacts/functions
 */
function get_ldap_value($fld, $mod) {
    $current = $mod->get('current_ldap_contact');
    if (!is_array($current) || !array_key_exists('all_fields', $current)) {
        return '';
    }
    if (array_key_exists($fld, $current['all_fields'])) {
        return $current['all_fields'][$fld];
    }
    return '';
}