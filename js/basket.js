$(function(){
function esc(x){return String(x==null?'':x).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
function money(x){return '£'+Number(x||0).toFixed(2);}
function err(x){if(window.toastr)toastr.error(x);else alert(x);}
function controls(r){return '<div class="basket-qty" data-ean="'+esc(r[0])+'" data-supplier="'+esc(r[7])+'"><button class="qty-minus" type="button" aria-label="Decrease quantity">−</button><span>'+esc(r[4])+'</span><button class="qty-plus" type="button" aria-label="Increase quantity">+</button></div>';}
function remove(r){return '<button type="button" class="btn btn-outline-danger btn-sm remove-line" data-ean="'+esc(r[0])+'" data-supplier="'+esc(r[7])+'">Remove</button>';}
function load(){
$.ajax({url:'functions/baskettable.php',method:'POST',dataType:'json'}).done(function(rows){
if(!Array.isArray(rows)){err('Basket response invalid.');return;}
var sub=0,desktop='<div class="basket-desktop table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Manufacturer</th><th>Description</th><th>Unit price (exc VAT)</th><th>Qty</th><th>Line total</th><th></th></tr></thead><tbody>',mobile='<div class="basket-mobile">';
rows.forEach(function(r){var price=Number(r[3]),qty=Number(r[4]);sub+=price*qty;
desktop+='<tr><td class="fw-bold">'+esc(r[6])+'</td><td>'+esc(r[2])+'</td><td>'+money(price)+'</td><td>'+controls(r)+'</td><td>'+money(price*qty)+'</td><td>'+remove(r)+'</td></tr>';
mobile+='<article class="basket-card"><strong>'+esc(r[6])+'</strong><div class="mb-3">'+esc(r[2])+'</div><div class="d-flex justify-content-between align-items-center gap-2 flex-wrap"><div><small class="text-muted">Unit price (exc VAT)</small><div class="basket-price">'+money(price)+'</div></div>'+controls(r)+'</div><div class="d-flex justify-content-between mt-3"><span>Line total</span><strong>'+money(price*qty)+'</strong></div><div class="mt-3">'+remove(r)+'</div></article>';
});
desktop+='</tbody></table></div>';mobile+='</div>';
var totals='<div class="basket-summary border-top pt-3"><div class="d-flex justify-content-between"><span>Subtotal (exc VAT)</span><strong>'+money(sub)+'</strong></div><div class="d-flex justify-content-between"><span>VAT (20%)</span><strong>'+money(sub*.2)+'</strong></div><div class="d-flex justify-content-between fs-5 mt-2"><strong>Total (inc VAT)</strong><strong>'+money(sub*1.2)+'</strong></div></div>';
var actions='<div class="d-flex justify-content-between flex-wrap gap-2 mt-4"><button class="btn btn-outline-danger deleteall">Delete basket</button><button class="btn btn-primary checkout">Proceed to checkout</button></div>';
$('#resultsDiv').html(rows.length?desktop+mobile+totals+actions:'<div class="alert alert-info">Your basket is empty.</div><a class="btn btn-primary" href="stock-multi.php">Search tyres</a>');
}).fail(function(){err('Could not load basket.');});
}
$(document).on('click','.qty-plus,.qty-minus',function(){var c=$(this).closest('.basket-qty'),q=Number(c.find('span').text())+($(this).hasClass('qty-plus')?1:-1);if(q<1||q>99){err('Quantity must be between 1 and 99.');return;}$.ajax({url:'functions/basketfunctions.php',method:'POST',data:{operation:'Edit',basket_id:c.attr('data-ean'),supplier:c.attr('data-supplier'),quantity:q}}).done(function(t){if(String(t).startsWith('Error:'))err(t);else load();}).fail(function(x){err(x.responseText||'Unable to update quantity.');});});
$(document).on('click','.remove-line',function(){if(!confirm('Remove this tyre offer?'))return;$.ajax({url:'functions/basketdeletesingle.php',method:'POST',data:{id:$(this).attr('data-ean'),supplier:$(this).attr('data-supplier')}}).done(function(t){if(String(t).startsWith('Error:'))err(t);else load();}).fail(function(x){err(x.responseText||'Unable to remove tyre.');});});
$(document).on('click','.deleteall',function(){if(confirm('Delete the entire basket?'))$.post('functions/basketdeleteall.php').done(load).fail(function(){err('Unable to clear basket.');});});
$(document).on('click','.checkout',function(){location.href='checkout.php';});
load();
});
