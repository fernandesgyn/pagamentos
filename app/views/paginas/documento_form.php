<?php
$pageBackUrl='/documentos';
require BASE_PATH.'/app/views/components/page_actions.php';
$obrigacaoSelecionada=(int)($obrigacaoSelecionada??($_GET['obrigacao_id']??0));
$fornecedorSelecionado=0;
foreach($obrigacoes as $ob){if((int)$ob['id']===$obrigacaoSelecionada){$fornecedorSelecionado=(int)$ob['fornecedor_id'];break;}}
$fornecedorComboboxId='documento-fornecedor';
?>
<form method="post" action="/documentos" class="card card-primary card-outline">
  <div class="card-header"><h3 class="card-title">Dados do documento</h3></div>
  <div class="card-body">
    <?=Csrf::field()?>
    <div class="row g-3">
      <div class="col-md-6">
        <?php require BASE_PATH.'/app/views/components/fornecedor_combobox.php';?>
      </div>

      <div class="col-md-6">
        <label class="form-label">Obrigação *</label>
        <select id="obrigacao_id" name="obrigacao_id" class="form-select" required data-obligation-select>
          <option value="">Selecione primeiro o fornecedor</option>
          <?php foreach($obrigacoes as $o):?>
            <option value="<?=e($o['id'])?>" data-fornecedor-id="<?=e($o['fornecedor_id'])?>" <?=((int)$o['id']===$obrigacaoSelecionada)?'selected':''?>><?=e($o['tipo'])?> <?=e($o['numero'])?>/<?=e($o['ano'])?> — <?=money($o['valor_total'])?></option>
          <?php endforeach;?>
        </select>
        <div class="form-text">Após selecionar o fornecedor, aparecem somente as obrigações cadastradas para ele.</div>
      </div>

      <div class="col-md-6 col-lg-4 col-xl"><label class="form-label">Tipo do documento *</label><select name="tipo_documento_id" class="form-select" required><option value="">Selecione</option><?php foreach($tipos as $t):?><option value="<?=e($t['id'])?>"><?=e($t['nome'])?></option><?php endforeach;?></select></div>
      <div class="col-md-6 col-lg-4 col-xl"><label class="form-label">Número *</label><input name="numero" class="form-control" required></div>
      <div class="col-md-6 col-lg-4 col-xl"><label class="form-label">Emissão do documento *</label><input type="date" name="data_emissao" value="<?=date('Y-m-d')?>" class="form-control" required></div>
      <div class="col-md-6 col-lg-4 col-xl"><label class="form-label">Data do atesto *</label><input type="date" name="data_atesto" class="form-control" required></div>
      <div class="col-md-6 col-lg-4 col-xl"><label class="form-label">Data Envio à COOINSP *</label><input type="date" name="data_envio_cooinsp" class="form-control" required></div>
      <div class="col-md-6 col-lg-4 col-xl"><label class="form-label">Valor bruto *</label><input name="valor_bruto" class="form-control" required placeholder="0,00"></div>
      <div class="col-md-6 col-lg-4 col-xl"><label class="form-label">Valor líquido *</label><input name="valor_liquido" class="form-control" required placeholder="0,00"></div>
      <div class="col-12"><div class="form-text">A data e hora do lançamento são registradas automaticamente pelo sistema.</div></div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end gap-2">
    <a href="/documentos" class="btn btn-outline-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar</button>
  </div>
</form>
<?php unset($obrigacaoSelecionada,$fornecedorSelecionado,$fornecedorComboboxId,$ob);?>
