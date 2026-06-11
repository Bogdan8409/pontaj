<?php
$conn = new mysqli("localhost","user","pass","db");
$ids = $_POST['ids'];

$idList = implode(',', array_map('intval',$ids));

$conn->query("UPDATE pontaje SET status='Aprobat' WHERE id IN ($idList)");
echo json_encode(['success'=>true]);

$('#bulkApprove').on('click', function(){
  const ids = $('.row-check:checked').map((_,el)=>el.value).get();

  if(!confirm(`Aprobi ${ids.length} pontaje?`)) return;

  $.post('ajax/bulk-approve.php', {ids}, () => {
    table.ajax.reload();
  });
});