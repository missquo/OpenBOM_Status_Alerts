# OpenBOM Email Status Alerts

These PHP scripts monitor a variety of information in OpenBOM using [OpenBOM API documentation](https://help.openbom.com/api/ "OpenBOM API documentation"). They are executed from our server using cron jobs via cPanel.  Scripts access our OpenBOM company account using my OpenBOM account and an API key issued by OpenBOM.  Login information is kept in a separate file.

## Current Alerts
___
**pdmAssignmentAlert.php**

Description: Daily update to assigned individuals for custom parts in any review status

If:  
&nbsp;&nbsp;&nbsp;IPL PART = 1  
&nbsp;&nbsp;&nbsp;And  
&nbsp;&nbsp;&nbsp;PDM STATE includes "REVIEW"  
Then:  
&nbsp;&nbsp;&nbsp;If:  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PDM ASSIGNMENT <> empty  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Or  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PDM ASSIGNMENT <> null  
&nbsp;&nbsp;&nbsp;Then:  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Email assigned person from PDM ASSIGNMENT  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(daily weekdays, morning, except one employee who is Mondays only)  
&nbsp;&nbsp;&nbsp;If:  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PDM ASSIGNMENT = empty  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Or  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PDM ASSIGNMENT = null  
&nbsp;&nbsp;&nbsp;Then:  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Email entire engineering team (daily weekdays, morning)  
