<?php namespace Winterpk\ContactBook\Components;
require 'vendor/autoload.php';

use Cms\Classes\ComponentBase;
use Winterpk\ContactBook\Models\Contact;
use Rainlab\User\Models\User;
use Auth;
use Validator;
use Request;
use Response;
use Redirect;
use Flash;
use Session; 
use Config;
use Hash; 
use File;
use Carbon\Carbon;
use League\Csv\Reader;
use League\Csv\Writer;

class ContactBook extends ComponentBase
{
	
    public function componentDetails()
    {
        return [
            'name'        => 'Contact Book',
            'description' => 'Displays a contact book interface.'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

	/**
	 * Add styles and javascript
	 * 
	 */
	public function onRun()
	{
		$user = Auth::getUser();
		
		// Check if this is an export
		$post = post();
		if (!empty($post['ids'])) {
			$cb_export_ids = $post['ids'];
			$date = Carbon::now()->toDateString();
			
			// This is an export, build a temp csv file and send to browser
			$hash = uniqid(rand());
			$filename = $hash . '.csv';
			$contacts = Contact::whereIn('id', $cb_export_ids)->get()->toArray();
			$path = storage_path() . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $filename;
			$contents = 'First Name,Last Name,Email,Street Address,City,State,Zip,Phone' . "\r\n";
			foreach ($contacts as $contact) {
				$contents .= $contact['first_name'] . ',';
				$contents .= $contact['last_name'] . ',';
				$contents .= $contact['email'] . ',';
				$contents .= $contact['street_address'] . ',';
				$contents .= $contact['city'] . ',';
				$contents .= $contact['state'] . ',';
				$contents .= $contact['zip'] . ',';
				$contents .= $contact['phone'] . "\r\n";
			}
			$bytes_written = File::put($path, $contents);
			if ($bytes_written) {
				$headers = array(
			        'Content-Type: application/pdf',
			        'Content-Disposition:attachment; filename="' . $filename . '"',
			        'Content-Transfer-Encoding:binary',
			        'Content-Length:' . filesize($path),
			    );
				return Response::download($path, $date . '-export.csv');
			} else {
				exit;
			}
		}

		// Check if its an import
		$import_file = Request::file('import');
		if ($import_file) {
			
			// Build an array for new contacts
			$new_contacts = array();
			
			// Make sure its the proper mime type
			$mime_type = $import_file->getMimeType();
			if ($mime_type == 'application/csv' || $mime_type == 'text/csv' || $mime_type == 'text/plain') {
				$reader = Reader::createFromPath($import_file);
				$mapping = Config::get('winterpk.contactbook::mapping');
				
				// Get the first row, usually the CSV header
				$headers = $reader->fetchOne();
				$reader->setFlags(\SplFileObject::READ_AHEAD|\SplFileObject::SKIP_EMPTY);
				$count = 0;
				
				// Now loop 3 times through the rows, columns then the mapping file
				foreach ($reader as $index => $row) {
					$new_contact = array();
					if ($index == 0) 
						continue;
					$contact = array();
					foreach ($row as $key => $column) {
						if (empty($headers[$key])) {
							continue;
						}
						$csv_col_header = $headers[$key];
						$live_header = false;
						foreach ($mapping as $map_header => $map_arr) {
							if (in_array(trim($csv_col_header), $map_arr)) {
								$live_header = $map_header;
								break;
							}
						}
						if ($live_header) {
							$contact[$live_header] = $column;
							$contact['user_id'] = $user->id;
						}
					}
					if ($contact) {
						$count++;
						$new_contacts[] = $contact;
						
					}
				}
				if ($new_contacts) {
					Contact::insert($new_contacts);
					Flash::success($count . ' contacts inserted');
				}
			} else {
				Flash::error('Invalid mime type');
			}
			
		}
		
		// Include Datatables assets
		//$this->addCss('/plugins/winterpk/contactbook/assets/css/jquery.dataTables.css');
		//$this->addCss('/plugins/winterpk/contactbook/assets/css/jquery.dataTables_themeroller.css');
		$this->addJs('/plugins/winterpk/contactbook/assets/js/jquery.dataTables.min.js');
		$this->addJs('/plugins/winterpk/contactbook/assets/js/jquery.maskedinput.min.js');
		$this->addJs('/plugins/winterpk/contactbook/assets/js/bootstrap-filestyle.min.js');
		
		// Include custom assets
		$this->addCss('/plugins/winterpk/contactbook/assets/css/contact-book.css');
		$this->addJs('/plugins/winterpk/contactbook/assets/js/contact-book.js');
	}
	
	public function contacts()
	{
		if (!Auth::check()) {
			return false;
		}
		$user = Auth::getUser();
		return Contact::where('user_id', $user->id)->get()->toArray();
	}
	
	public function onCreate()
	{
		if (!Auth::check()) {
			return false;
		}
		$user = Auth::getUser();
		$post = post();
		$contact = new Contact;
		$user_id = $user->id;
		if ( ! $user_id) {
			Flash::error('Error saving contact');
			return Redirect::to(Request::url());
		}
		$contact->user_id = $user_id;
		
		// Validate input
		$validation = Validator::make($post, $contact->rules, $contact->messages);
		if ($validation->fails()) {	
			return array('errors' => $validation->errors()->toArray());
		}
		foreach ($post as $key => $value) {
			$contact->$key = trim(preg_replace('/\s+/', ' ', $value));
		}
		$contact->save();
		Flash::success('New contact added');
		return Redirect::to(Request::url());
	}

	public function onEditButton()
	{
		$post = post();
		if ( ! isset($post['id'])) {
			return 'error';
		}
		$contact = Contact::where('id', $post['id'])->get()->toArray();
		if ( ! $contact) {
			return 'error';
		}
		return ['#cb-modal-edit-wrap' => $this->renderPartial('contactbook::edit', array('contact' => $contact[0]))];
	}	
	
	public function onEditSave()
	{
		$post = post();
		if ( ! isset($post['contact_id'])) {
			return 'error';
		}
		$id = $post['contact_id'];
		unset($post['contact_id']);
		$contact = Contact::find($id);
		$validation = Validator::make($post, $contact->rules, $contact->messages);
		if ($validation->fails()) {	
			return array('errors' => $validation->errors()->toArray());
		}
		foreach ($post as $key => $value) {
			$contact->$key = trim(preg_replace('/\s+/', ' ', $value));
		}
		$contact->save();
		Flash::success('Contact updated');
		return Redirect::to(Request::url());
	}
	
	public function onDelete()
	{
		$post = post();
		if ( ! isset($post['ids'])) {
			return 'error';
		}
		Contact::whereIn('id', $post['ids'])->delete();
		Flash::success('Contacts deleted');
		return Redirect::to(Request::url());
	}
}