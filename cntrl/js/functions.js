function details( id ) {
 var request_headers = JSON.parse( gei( 'headers'+id ) );
 var warnings = JSON.parse( gei( 'warnings'+id ) );
 var dets = gei( 'details', true );

 dets.innerHTML = '';
 dets.style.display = 'block';
 dets.style.visibility = 'visible';

 var html = '<div class="details_close" onclick="hide_details();">[x]</div>';
 html += '<div class="details_title">Request Headers</div><br>';

 for(var k in request_headers) {
  html += '<div class="details_sec"><span class="details_header">['+k+'] </span><span class="details_value">'+request_headers[k]+'</span></div><br>';  
 }

 html += '<div class="details_title">Actions</div><br>';
 html += '<div class="details_sec"><span class="details_header">Ban IP Address:</span><span class="details_value"><a href="ban.php?type=ip&id='+id+'">Block</a></span></div><br>';
 html += '<div class="details_sec"><span class="details_header">Ban User Agent:</span><span class="details_value"><a href="ban.php?type=ua&id='+id+'">Block</a></span></div><br>';

 html += '<div class="details_title">Warnings / Deviations</div><br>';
 for(var k in warnings) {
  message = ( warnings[k]=='No warnings or deviations.' ? 'NONE' : 'WARNING' )
   
  html += '<div class="details_sec"><span class="details_header">['+message+'] </span><span class="details_value">'+warnings[k]+'</span></div><br>';
 }

 dets.innerHTML = html;
}

function hide_details() {
 var dets = gei( 'details', true );
 dets.style.display = 'none';
 dets.style.visibility = 'hidden';
}

function gei( id, obj=null ) {
 if( obj!=null )
  return document.getElementById( id );
 if( document.getElementById( id ).value!=null )
  return document.getElementById( id ).value;
 else return document.getElementById( id ).innerHTML;
}
