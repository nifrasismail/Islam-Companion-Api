var BasicSite = {	
	/**
	 * It is called when the user clicks on a delete link
	 * 
	 * @param string url the url used to delete the item	 
	 */	
	DeleteItem: function(url)
		{
			try {
					/** The user is asked to confirm if he wants to delete the item */
					if(confirm('Are you sure you want to proceed ?')) {					    
					    arguments = {}				
					    /** The ajax call for deleting the item */			
				        Utilities.MakeAjaxCall(url,"GET",arguments,Callbacks.DeleteItemCallback,Callbacks.ErrorCallBack);
				   }
			}
			catch(err) {
				/** If there was an error then the error callback is called */
			    Callbacks.ErrorCallBack();
			}	
	 }
};
