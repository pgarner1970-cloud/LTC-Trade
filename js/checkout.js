$(function () {
  function esc(v){return String(v==null?'':v).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
  function money(v){return '£'+Number(v||0).toFixed(2);}
  function error(msg){if(window.toastr)toastr.error(msg);else alert(msg);}
  $.when(
    $.ajax({url:'functions/userfetchsingle.php',method:'POST',dataType:'json'}),
    $.ajax({url:'functions/baskettable.php',method:'POST',dataType:'json'})
  ).done(function(userResult,basketResult){
    var user=userResult[0]||{}, rows=basketResult[0]||[];
    if(!Array.isArray(rows)){error('Unable to load basket details.');return;}
    var date=new Date(), d=String(date.getFullYear())+String(date.getMonth()+1).padStart(2,'0')+String(date.getDate()).padStart(2,'0');
    var ref=String(user.username||'')+'-'+d, subtotal=0;
    var html='<div class="row g-3 align-items-stretch"><div class="col-12 col-lg-6"><section class="checkout-summary"><h4 class="mb-3">Order summary</h4>';
    rows.forEach(function(r){
      var price=Number(r[3]), qty=Number(r[4]);
      subtotal+=price*qty;
      html+='<div class="checkout-item"><div class="fw-bold">'+esc(r[6]||'Tyre')+'</div><div>'+esc(r[2])+'</div><div class="d-flex justify-content-between gap-2 mt-2"><span>'+qty+' × '+money(price)+' ex VAT</span><strong class="checkout-price">'+money(qty*price)+'</strong></div></div>';
    });
    if(!rows.length) html+='<p class="text-muted">Your basket is empty.</p>';
    html+='<div class="checkout-totals"><div class="d-flex justify-content-between"><span>Subtotal (ex VAT)</span><strong>'+money(subtotal)+'</strong></div><div class="d-flex justify-content-between"><span>VAT (20%)</span><strong>'+money(subtotal*.2)+'</strong></div><div class="d-flex justify-content-between total-pay mt-2"><strong>Total (inc VAT)</strong><strong>'+money(subtotal*1.2)+'</strong></div></div><a class="btn btn-outline-primary mt-3" href="basket.php">← Back to basket</a></section></div>';
    html+='<div class="col-12 col-lg-6"><section class="checkout-details"><h4 class="mb-3">Order details</h4><form id="checkout_form" method="post">';
    html+='<input type="hidden" name="username" value="'+esc(user.username)+'">';
    html+='<div class="mb-3"><label for="order_name" class="form-label">Name</label><input class="form-control" id="order_name" name="order_name" autocomplete="name" required maxlength="150" placeholder="Enter your name"></div>';
    html+='<div class="mb-3"><label for="order_ref" class="form-label">Your reference</label><input class="form-control" id="order_ref" name="order_ref" required maxlength="150" value="'+esc(ref)+'"></div>';
    html+='<div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email" autocomplete="email" value="'+esc(user.email)+'"></div>';
    html+='<div class="form-check mb-4"><input class="form-check-input" type="checkbox" id="agree" name="agree" required><label class="form-check-label" for="agree">I agree to the Terms &amp; Conditions of Sale</label></div>';
    html+='<button class="btn btn-primary checkout-submit" id="submitbtn" type="submit" '+(!rows.length?'disabled':'')+'>Submit Order</button><p id="checkout-status" class="small mt-2" role="status" aria-live="polite"></p></form></section></div></div>';
    $('#resultsDiv').html(html);
  }).fail(function(){error('Could not load checkout. Please return to your basket and try again.');});
  $(document).on('submit','#checkout_form',function(e){
    e.preventDefault();
    var form=this;
    if(!form.checkValidity()){form.reportValidity();return;}
    var $btn=$('#submitbtn');
    if($btn.prop('disabled'))return;
    $btn.prop('disabled',true).text('Submitting…');
    $('#checkout-status').text('Please wait while your order is submitted.');
    $.ajax({url:'functions/basketcheckout.php',method:'POST',data:$(form).serialize(),dataType:'text'})
      .done(function(response){
        if(String(response).trim()==='Order placed.'){
          window.location.href='index.php';
        }else{
          error(String(response)||'Order was not confirmed.');
          $('#checkout-status').text('Order not confirmed. Please check before retrying.');
          $btn.prop('disabled',false).text('Submit Order');
        }
      }).fail(function(xhr){
        error(xhr.responseText||'Unable to confirm order. Check order history before trying again.');
        $('#checkout-status').text('Submission status uncertain. Check order history before retrying.');
        $btn.prop('disabled',false).text('Submit Order');
      });
  });
});
