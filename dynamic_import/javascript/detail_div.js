
/*
Simple Image Trail script- By JavaScriptKit.com
Visit http://www.javascriptkit.com for this script and more
This notice must stay intact
*/

var traildiv=["sachendiv", 400, 300] //div name, plus width and height of maximum sachen-image
var offsetfrommouse=[10,-200] //image x,y offsets from cursor position in pixels. Enter 0,0 for no offset
var displayduration=0 //duration in seconds image should remain visible. 0 for always.

if (document.getElementById || document.all)
document.write('<div id="sachendiv" style="position:absolute;visibility:hidden;display:none;left:0px;top:0px;width:1px;height:1px;"></div>')

function gettraildiv(){
 if (document.getElementById)
  return document.getElementById("sachendiv")
 else if (document.all)
  return document.all.sachendiv
}

function gettraildivstyle(){
 return gettraildiv().style
}

function truebody(){
return (!window.opera && document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function hidetrail(){
gettraildivstyle().visibility="hidden"
document.onmousemove=""

}

function followmouse(e){
 var xcoord=offsetfrommouse[0]
 var ycoord=offsetfrommouse[1]
 if (typeof e != "undefined"){
  xcoord+=e.pageX
  ycoord+=e.pageY
 }
 else if (typeof window.event !="undefined"){
  xcoord+=truebody().scrollLeft+event.clientX
  ycoord+=truebody().scrollTop+event.clientY
 }

 var docwidth=document.all? truebody().scrollLeft+truebody().clientWidth : pageXOffset+window.innerWidth-15
 var docheight=document.all? Math.max(truebody().scrollHeight, truebody().clientHeight) : Math.max(document.body.offsetHeight, window.innerHeight)
 if (xcoord+traildiv[1]+3>docwidth || ycoord+traildiv[2]> docheight)
  gettraildivstyle().display="none"
 else 
  gettraildivstyle().display=""
 gettraildivstyle().left=xcoord+"px"
 gettraildivstyle().top=ycoord+"px"
}

document.onmousemove=followmouse

if (displayduration>0)
setTimeout("hidetrail()", displayduration*1000)

function showSachenImage(imageName) {
 if ( gettraildivstyle().visibility == 'hidden' ) {
   gettraildiv().innerHTML = '<img src="'+imageName+'" style="border:1px solid white;">';
   gettraildivstyle().visibility="visible";
   gettraildivstyle().display="block";
 }
}

function hideSachenImage() {
 gettraildiv().innerHTML = '';
 gettraildivstyle().visibility="hidden";
 gettraildivstyle().display="none";
}
 
