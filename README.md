# MathCaptcha

A simple maths based captcha tool that is lightweight and written in vanilla JS and plain PHP, no plugins needed :)

Online demo: https://morcreate.net/github/MathCaptcha/

## Requirements
This captcha tool has been written in vanilla JS and PHP with no extra packages/plugins needed so you are able to get setup quickly and integrate it easily into your project.

## Demo Installation
To run the demo on your local machine, do the following:
- Clone this repo
- Create a table in your database with 3 columns: uid (string), answer (int), created (int)
- Enter the captcha.php file (demo/src/captcha.php) and edit line 3 (database table name) and lines 12-16 (mysql connection info)
- Open the index.php file (demo/index.php) on your machine

## How To Use
Place the captcha.js and the captcha.php file on your website. Then, create an SQL table in your database with 3 columns: uid (string), answer (int), created (int).

### Places to edit
**captcha.js**
- Edit the URL variable on line 4 to provide a path to the captcha.php file
- **Optional** - Change the SUPPRESS_CONSOLE variable on line 3 to false to log data to the console that may be helpful for deubugging

**captcha.php**
- Edit line 3 and lines 12-16 with the connection info of your database
- **Optional** - Change the bgColor and textColor variables on lines 5 and 6 - RGB color code [R, G, B]
- **Optional** - Change the autoDeleteRecords variable on line 8 to false to stop the autodeletion of database records. Note: records are still deleted when the validate function is called (no matter if the user gets the calculation correct)

### Adding MathCaptcha to your form
To add MathCaptcha to your form then just add an image with the data-mathcaptcha attribute and an input element for the users answer like below (Bootstrap classes used for quick styling):
```html
<div class="form-group">
  <img class="img-thumbnail" data-mathcaptcha="#inputForImage">
  <input type="number" class="form-control" placeholder="Answer" id="inputForImage" required>
</div>

<script src="path/to/captcha.js">
```

### Validating the captcha
There are 2 main ways to validate the captcha, either by requiring the captcha.php file or by sending a get or post request to the captcha.php file

**Requiring the captcha.php file (recommended)**

The captcha variables that you will receive when you submit the form are: 'captcha_uid' and 'captcha_answer', these two input variables are automatically created by the captcha.js file.
Implement the verification into your PHP code something like this:
```php
require 'captcha.php';
 
$result = captcha_validate($_POST['captcha_uid'], $_POST['captcha_answer']);
if ($result === 1) {
    // the user is not a robot
} else if ($result === 0) {
    // the user is likely a robot or just really bad at maths
} else {
    // the id doesn't exist or may have expired/deleted
}
```

**Send a get or post request to the captcha.php file (not recommended)**

Send the following data to the captcha.php file as a get or post request:
- 'o' : 'validate'
- 'i' : the unique id
- 'a' : the users answer to the calculation
```
path/to/captcha.php?o=validate&i=XXXXXXXXXXXXX&a=0
```
Return values:
- 1 : the answer is correct
- 0 : the answer is incorrect
- 'Invalid ID' : The id is invalid
