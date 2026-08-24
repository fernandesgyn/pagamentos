<?php
$_pageActionsBackUrl=(string)($pageBackUrl??'');
$_pageActionsBackLabel=(string)($pageBackLabel??'Voltar');
$_pageActionsRight=is_array($pageRightActions??null)?$pageRightActions:[];
?>
<?php if($_pageActionsBackUrl!==''||$_pageActionsRight!==[]):?>
<div class="portal-page-actions d-print-none">
  <div class="portal-action-group portal-page-actions-left">
    <?php if($_pageActionsBackUrl!==''):?>
      <a class="btn btn-sm btn-outline-secondary" href="<?=e($_pageActionsBackUrl)?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i><?=e($_pageActionsBackLabel)?></a>
    <?php endif;?>
  </div>
  <div class="portal-action-group portal-page-actions-right">
    <?php foreach($_pageActionsRight as $_pageActionsItem):?>
      <?php if(!is_array($_pageActionsItem)||trim((string)($_pageActionsItem['label']??''))==='')continue;?>
      <a class="<?=e((string)($_pageActionsItem['class']??'btn btn-sm btn-primary'))?>" href="<?=e((string)($_pageActionsItem['href']??'#'))?>">
        <?php if(!empty($_pageActionsItem['icon'])):?><i class="fa-solid <?=e((string)$_pageActionsItem['icon'])?>" aria-hidden="true"></i><?php endif;?><?=e((string)$_pageActionsItem['label'])?>
      </a>
    <?php endforeach;?>
  </div>
</div>
<?php endif;?>
<?php unset($_pageActionsBackUrl,$_pageActionsBackLabel,$_pageActionsRight,$_pageActionsItem);?>
