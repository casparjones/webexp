XHR.implement({
	isSuccess: handleResponse
})
var myXHR = new XHR({method: 'get'});
var glob_url;
var resp = new Array();

function handleResponse(value) {
  var response = myXHR.transport.responseText;
  var update = new Array();
  if(response.indexOf('|*exp*|' != -1)) {	
     update = response.split('|*exp*|');			
     updatePage(update)
     Lightbox.init.bind(Lightbox)
	}
}

function updatePage(update) {
    if(update.length>0){
		 document.getElementById(update[0]).innerHTML = update[1];
    }
    
		if(update.length>2){
			document.getElementById(update[2]).innerHTML = update[3];					
		}
		if(update.length>4){
			document.getElementById(update[4]).innerHTML = update[5];					
		}
}

function ajax_call(url) {
  var info = document.getElementById('exp_info');
  var newurl = url + '&view=ajax';
  var jetzt = new Date();
  var update;
  
  glob_url = url;

  
  info.innerHTML = "<img src='index.php?view=lb&v=loading16.gif' widht='14' height='14'>&nbsp;loading...";
  // window.location.hash = jetzt.getTime();
  // window.location.replace = url;

  if (false) { //!globalStorage['d4us.net'].glob_url) {
    response = globalStorage['d4us.net'].getItem(glob_url);
    console.log("load saved data");
    console.log(response);
    update = response.split('|*exp*|');			
    updatePage(update)
  } else {
    var url_data = newurl.split('?');
    myXHR.send(newurl);
  }
  console.log(myXHR);
  return false;
}

function newfolder() {
  var newfolder = document.getElementById('create_newfolder').value;
  alert(glob_folder);
	http.open('get', './index.php?folder='+glob_folder+'&new_folder='+newfolder+'&view=ajax&action=reload');
  http.onreadystatechange = handleResponse;
  http.send(null);
}

function changeMode(DirMode) {
  document.getElementById('exp_message').innerHTML = "<img src='index.php?view=lb&v=loading' widht='14' height='14'>&nbsp;loading...";
  var newdirmode=2;
  
  if(DirMode==2) {
    newdirmode=1;
  }
  document.getElementById('dirmode_1').style.backgroundColor="white";
  document.getElementById('dirmode_2').style.backgroundColor="white";
  document.getElementById('dirmode_'+newdirmode).style.backgroundColor="yellow";

	http.open('get', './index.php?folder='+glob_folder+'&view=ajax&DirMode='+DirMode+'&action=reload');
  http.onreadystatechange = handleResponse;
  http.send(null);
}

var Effect = new Object();  

Effect.toggle = function(id,operation,option) {
    var obj = document.getElementById(id);
    if(obj.style.display == "none") {
      obj.style.display = "block";
      var mySlider = new Fx.Slide(id);
      mySlider.toggle() //toggle the slider up and down.
    } else {
      // obj.style.display = "none"
      var mySlider = new Fx.Slide(id);
      mySlider.toggle() //toggle the slider up and down.
    } 
  }
