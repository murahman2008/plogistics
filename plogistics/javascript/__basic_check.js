var __basic_check = __basic_check || {};

/// constructor ///
__basic_check = function() {
	this.criteriaAttribute = 'basic_check_criteria';
	this.className = 'basic_check';
	this.criteriaSplitter = '|';
};

/**
 * This function validates the form being passed to the function 
 */
__basic_check.prototype.validateForm = function(form) {
	var tmp = {};
	tmp.errors = [];
	var self = this;
	
	$(form).find('.' + self.className).each(function(index, item) {
		tmp.criteria = $.trim($(item).attr(self.criteriaAttribute));
		if(tmp.criteria !== '')
		{
			tmp.criteriaArray = tmp.criteria.split(self.criteriaSplitter);
			tmp.itemValue = $.trim($(item).val());
			
			for(var i = 0; i < tmp.criteriaArray.length; i++)
			{
				if(tmp.criteriaArray[i] == 'required')
				{
					if(tmp.itemValue == '')
					{
						tmp.errors.push($(item).attr('name') + ' is required');
						break;	
					}
					else
						continue;
				}
				if(tmp.criteriaArray[i] == 'numeric')
				{
					if(isNaN(tmp.itemValue))
					{
						tmp.errors.push($(item).attr('name') + ' must be numeric');
						break;	
					}
					else
						continue;
				}	
				if(tmp.criteriaArray[i] == 'email')
				{
					if(!_isValidEmailAddress(tmp.itemValue))
					{
						tmp.errors.push($(item).attr('name') + ' must be a valid email');
						break;	
					}
					else
						continue;
				}	
			}
		}	
	});
	
	if(tmp.errors.length)
	{
		alert(tmp.errors.join("\n"));
		return false;
	}
	
	return true;
};

/// create an object of the __basic_check CLASS ///
var basic_check = new __basic_check();

