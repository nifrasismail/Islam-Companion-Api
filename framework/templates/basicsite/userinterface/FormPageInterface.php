<?php

namespace Framework\Templates\BasicSite\UserInterface;

use Framework\Configuration\Base as Base;

/**
 * This class implements the FormPageInterface interface
 * It extends the Base class
 * 
 * It provides interface for forms
 * Each form class should implement the FormPageInterface
 * 
 * @category   Application
 * @package    FormPageInterface
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
interface FormPageInterface
{	
	/**
	 * Used to return hidden form fields
	 *
	 * It returns the form fields that need to be hidden
	 *
	 * @return array $hidden_form_field_list list of form fields that need to be hidden
	 */
	public function GetHiddenFormFields();
	/**
	 * Used to update the form data before it is saved to database
	 * It should be overriden by a child class
	 * It updates the form data before it is saved to database
	 *
	 * @param array $data the row data to be filtered
	 * @param array $parameters the form page parameters	 
	 *
	 * @return array $form_data the filtered form data to be saved
	 */
	public function FilterSaveData($data, $parameters);
	/**
	 * It is used to save form data
	 *
	 * It saves the form data to database
	 *
	 * @param array $parameters the application parameters
	 *
	 * @return $is_saved boolean indicates if the data was successfully saved
	 */
	public function SaveFormData($form_data, $parameters);
	/**
	 * Used to delete data from database
	 *
	 * It deletes the given data	 
	 *
	 * @param array $parameters the application parameters
	 *
	 * @return string $redirect_url the url at which the user is redirected
	 */
	public function DeleteData($parameters);
	/**
	 * Used to get the selectbox option values
	 *
	 * It returns the values for a selectbox
	 *
	 * @param string $field_name the name of the selectbox field
	 *
	 * @return array $selectbox_values the selectbox values
	 */
	public function GetSelectBoxValues($field_name);
	/**
	 * Used to get the value for form onsubmit event
	 *
	 * It returns the form onsubmit event handler
	 *
	 * @param array $parameters the form page parameters
	 *
	 * @return string $form_onsubmit the form onsubmit event handler
	 */
	public function GetFormOnsubmit($parameters);
}
