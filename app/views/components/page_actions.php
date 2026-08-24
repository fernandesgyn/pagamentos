<?php
$pageBackUrl=(string)($pageBackUrl??'');
$pageBackLabel=(string)($pageBackLabel??'Voltar');
$pageRightActions=is_array($pageRightActions??null)?$pageRightActions:[];
?>
<?php if($pageBackUrl!==''||$pageRightActions!==[]):?>
<div class="portal-page-actions d-print-none">
  <div class="portal-action-group portal-page-actions-left">
    <?php if($pageBackUrl!==''):?>
      <a class="btn btn-sm btn-outline-secondary" href="<?=e($pageBackUrl)?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i><?=e($pageBackLabel)?></a>
    <?php endif;?>
  </div>
  <div class="portal-action-group portal-page-actions-right">
    <?php foreach($pageRightActions as $action):?>
      <?php if(!is_array($action)||trim((string)($action['label']??''))==='')continue;?>
      <a class="<?=e((string)($action['class']??'btn btn-sm btn-primary'))?>" href="<?=e((string)($action['href']??'#'))?>">
        <?php if(!empty($action['icon'])):?><i class="fa-solid <?=e((string)$action['icon'])?>" aria-hidden="true"></i><?php endif;?><?=e((string)$action['label'])?>
      </a>
    <?php endforeach;?>
  </div>
</div>
<?php endif;?>
<?php unset($pageBackUrl,$pageBackLabel,$pageRightActions,$action);?>
