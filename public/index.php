<?php
  // Protege a página e expõe $AUTH_USER_NAME/$AUTH_USER_ID da sessão
  require_once __DIR__ . '/require_login.php';
  $usuario = $AUTH_USER_NAME ?? 'Usuário';
?>
<!doctype html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autopeças — Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="bg-light">
    <style>
      .app-shell { min-height: 100vh; }
      .app-sidebar { width: 230px; background:#0f172a; min-height: 100vh; }
      .app-sidebar .nav-link { color:#cbd5e1; }
      .app-sidebar .nav-link.active, .app-sidebar .nav-link:hover { color:#fff; background:rgba(255,255,255,.06); }
      .panel-bar { background: linear-gradient(90deg,#0ea5e9,#2563eb); color:#fff; border-radius:.5rem; }
      .panel-bar .title { font-weight:600; }
      .table thead th { font-size:.85rem; text-transform:uppercase; letter-spacing:.03em; }
      .badge-soft-danger { background: #fee2e2; color:#b91c1c; }
      .btn-soft { background: rgba(255,255,255,.15); color:#fff; border:0; }
      .btn-soft:hover { background: rgba(255,255,255,.25); color:#fff; }
    </style>

    <div class="container-fluid app-shell p-0">
      <div class="d-flex">
        <!-- Sidebar -->
        <aside class="app-sidebar d-none d-md-block py-3">
          <div class="px-3 mb-3 text-white-50 small" id="user-name"><?php echo htmlspecialchars($usuario); ?></div>
          <nav class="nav flex-column px-2" id="side-menu">
            <a class="nav-link active rounded" data-target="#produtos" href="#">Produtos</a>
            <a class="nav-link rounded" data-target="#mov" href="#">Movimentações</a>
            <a class="nav-link rounded" data-target="#relatorios" href="#">Relatórios</a>
          </nav>
        </aside>

        <!-- Main -->
        <main class="flex-fill p-3 p-md-4">
          <div class="panel-bar d-flex align-items-center justify-content-between p-3 px-4 mb-3 shadow-sm">
            <div class="title">Painel de Estoque</div>
            <div class="d-flex gap-2">
              <button class="btn btn-soft btn-sm" onclick="exportCSV()">Excel</button>
              <button class="btn btn-soft btn-sm" onclick="exportPDF()">PDF</button>
            </div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <div class="text-center h5 mb-3" id="section-title">Produtos</div>

              <div class="tab-content pt-1" id="tabsContent">
        <!-- Produtos -->
        <div class="tab-pane fade show active" id="produtos" role="tabpanel">
          <div class="d-flex align-items-center gap-2 mb-3">
            <input id="q" class="form-control" placeholder="Buscar por nome, SKU, categoria">
            <button class="btn btn-primary" onclick="loadProducts()">Buscar</button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalProduto" onclick="novoProduto()">+ Novo</button>
          </div>
          <div class="table-responsive bg-white rounded shadow-sm">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>SKU</th><th>Nome</th><th>Categoria</th><th class="text-end">Preço</th><th class="text-end">Mín.</th><th class="text-end">Estoque</th><th>Ações</th>
                </tr>
              </thead>
              <tbody id="tbody-produtos"></tbody>
            </table>
          </div>
        </div>

        <!-- Movimentações -->
        <div class="tab-pane fade" id="mov" role="tabpanel">
          <form id="form-mov" class="row g-3 bg-white p-3 rounded shadow-sm" onsubmit="return registrarMov(event)">
            <div class="col-md-4">
              <label class="form-label">Produto</label>
              <select id="mov-product" class="form-select" required></select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Tipo</label>
              <select id="mov-type" class="form-select" required>
                <option value="IN">Entrada</option>
                <option value="OUT">Saída</option>
                <option value="ADJUST">Ajuste</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Quantidade</label>
              <input type="number" id="mov-qty" class="form-control" min="1" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Ref./Motivo</label>
              <input type="text" id="mov-ref" class="form-control" placeholder="NF, venda, inventário...">
            </div>
            <div class="col-12 d-flex gap-2">
              <button class="btn btn-primary">Registrar</button>
              <button type="button" class="btn btn-outline-secondary" onclick="resetMov()">Limpar</button>
            </div>
          </form>
          <div class="alert alert-info mt-3">
            Dica: use <strong>Ajuste</strong> para correções de inventário (positivas ou negativas).
          </div>
        </div>

        <!-- Relatórios -->
        <div class="tab-pane fade" id="relatorios" role="tabpanel">
          <div class="bg-white p-3 rounded shadow-sm">
            <h6>Estoque — todos os produtos</h6>
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead class="table-light">
                  <tr><th>SKU</th><th>Produto</th><th class="text-end">Estoque</th><th class="text-end">Mín.</th></tr>
                </thead>
                <tbody id="tbody-alertas"></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
            </div>
          </div>
        </main>
      </div>
    </div>

    <!-- Modal Produto -->
    <div class="modal fade" id="modalProduto" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form id="form-prod" onsubmit="return salvarProduto(event)">
            <div class="modal-header">
              <h5 class="modal-title">Produto</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" id="prod-id">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">SKU</label>
                  <input id="prod-sku" class="form-control" required>
                </div>
                <div class="col-md-8">
                  <label class="form-label">Nome</label>
                  <input id="prod-name" class="form-control" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Categoria</label>
                  <input id="prod-category" class="form-control">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Preço</label>
                  <input type="number" step="0.01" id="prod-price" class="form-control" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Qtd. mínima</label>
                  <input type="number" id="prod-min" class="form-control" value="0">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary">Salvar</button>
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
    <script>
      const apiBase = './api';
      let produtosCache = [];

      function money(v){ return Number(v).toLocaleString('pt-BR',{minimumFractionDigits:2}); }

      async function loadProducts(){
        const q = document.getElementById('q').value;
        const res = await fetch(`${apiBase}/products_list.php?` + new URLSearchParams({q}));
        const data = await res.json();
        produtosCache = Array.isArray(data) ? data : [];
        const tbody = document.getElementById('tbody-produtos');
        tbody.innerHTML = '';
        produtosCache.forEach(p => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${p.sku}</td>
            <td>${p.name}</td>
            <td>${p.category ?? ''}</td>
            <td class="text-end">R$ ${money(p.price)}</td>
            <td class="text-end">${p.min_qty ?? 0}</td>
            <td class="text-end ${(+p.stock_qty <= +p.min_qty)?'text-danger fw-semibold':''}">${p.stock_qty ?? 0}</td>
            <td>
              <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary" onclick='editarProduto(${JSON.stringify(p)})'>Editar</button>
                <button class="btn btn-outline-danger" onclick='removerProduto(${p.id})'>Excluir</button>
              </div>
            </td>`;
          tbody.appendChild(tr);
        });
        // popular select de movimentações
        const sel = document.getElementById('mov-product');
        sel.innerHTML = produtosCache.map(p => `<option value="${p.id}">${p.sku} — ${p.name} (Est.: ${p.stock_qty ?? 0})</option>`).join('');
        // carregar alertas
        carregarAlertas(produtosCache);
      }

      function carregarAlertas(data){
        const body = document.getElementById('tbody-alertas');
        body.innerHTML = '';
        data.forEach(p => {
          const abaixo = (+p.stock_qty) <= (+p.min_qty);
          const tr = document.createElement('tr');
          tr.innerHTML = `<td>${p.sku}</td><td>${p.name}</td>
            <td class="text-end ${abaixo ? 'text-danger fw-semibold' : ''}">${p.stock_qty ?? 0}</td>
            <td class="text-end">${p.min_qty ?? 0}</td>`;
          body.appendChild(tr);
        });
      }

      function novoProduto(){
        document.getElementById('prod-id').value = '';
        document.getElementById('prod-sku').value = '';
        document.getElementById('prod-name').value = '';
        document.getElementById('prod-category').value = '';
        document.getElementById('prod-price').value = '0';
        document.getElementById('prod-min').value = '0';
      }

      function editarProduto(p){
        const modal = new bootstrap.Modal('#modalProduto');
        document.getElementById('prod-id').value = p.id;
        document.getElementById('prod-sku').value = p.sku;
        document.getElementById('prod-name').value = p.name;
        document.getElementById('prod-category').value = p.category ?? '';
        document.getElementById('prod-price').value = p.price;
        document.getElementById('prod-min').value = p.min_qty ?? 0;
        modal.show();
      }

      async function salvarProduto(e){
        e.preventDefault();
        const fd = new FormData();
        ['id','sku','name','category','price','min'].forEach(k => {});
        fd.append('id', document.getElementById('prod-id').value);
        fd.append('sku', document.getElementById('prod-sku').value);
        fd.append('name', document.getElementById('prod-name').value);
        fd.append('category', document.getElementById('prod-category').value);
        fd.append('price', document.getElementById('prod-price').value);
        fd.append('min_qty', document.getElementById('prod-min').value);
        const res = await fetch(`${apiBase}/products_create.php`, {method:'POST', body: fd});
        const data = await res.json();
        if (data.error) { alert(data.error); return; }
        bootstrap.Modal.getInstance(document.getElementById('modalProduto')).hide();
        loadProducts();
      }

      async function removerProduto(id){
        if (!confirm('Desativar este produto?')) return;
        const fd = new FormData(); fd.append('id', id);
        const res = await fetch(`${apiBase}/products_delete.php`, {method:'POST', body: fd});
        const data = await res.json();
        if (data.error) { alert(data.error); return; }
        loadProducts();
      }

      function resetMov(){
        document.getElementById('mov-type').value = 'IN';
        document.getElementById('mov-qty').value = '';
        document.getElementById('mov-ref').value = '';
      }

      async function registrarMov(e){
        e.preventDefault();
        const fd = new FormData();
        fd.append('product_id', document.getElementById('mov-product').value);
        fd.append('type', document.getElementById('mov-type').value);
        fd.append('qty', document.getElementById('mov-qty').value);
        fd.append('reason', document.getElementById('mov-ref').value);
        fd.append('ref_code', document.getElementById('mov-ref').value);
        const res = await fetch(`${apiBase}/stock_move.php`, {method:'POST', body: fd});
        const data = await res.json();
        if (data.error) { alert(data.error); return; }
        alert('Movimentação registrada!');
        loadProducts();
        resetMov();
      }

      loadProducts();

      function exportCSV(){
        if (!produtosCache.length) { alert('Nada para exportar.'); return; }
        const headers = ['SKU','Nome','Categoria','Preço','Mínimo','Estoque'];
        const rows = produtosCache.map(p => [p.sku, p.name, p.category ?? '', String(p.price).replace('.',','), p.min_qty ?? 0, p.stock_qty ?? 0]);
        const csv = [headers.join(';')].concat(rows.map(r => r.join(';'))).join('\n');
        const blob = new Blob(["\uFEFF"+csv], {type:'text/csv;charset=utf-8;'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = 'produtos.csv'; a.click();
        URL.revokeObjectURL(url);
      }

      function loadScriptOnce(src){
        return new Promise((resolve, reject) => {
          if (document.querySelector(`script[src="${src}"]`)) { resolve(); return; }
          const s = document.createElement('script'); s.src = src; s.async = true;
          s.onload = resolve; s.onerror = () => reject(new Error('Falha ao carregar '+src));
          document.head.appendChild(s);
        });
      }

      async function exportPDF(){
        try {
          if (!produtosCache.length) { alert('Nada para exportar.'); return; }
          // Garante bibliotecas carregadas (Edge pode atrasar o carregamento do CDN)
          if (!window.jspdf) {
            await loadScriptOnce('https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js');
          }
          if (!window.jspdf?.jsPDF || !window.jspdf?.API?.autoTable) {
            await loadScriptOnce('https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js');
          }
          const { jsPDF } = window.jspdf;
          const doc = new jsPDF('p','pt','a4');
          const title = 'Relatório de Estoque';
          doc.setFontSize(14);
          doc.text(title, 40, 40);
          const head = [['SKU','Produto','Categoria','Preço','Mín.','Estoque']];
          const body = produtosCache.map(p => [
            p.sku,
            p.name,
            p.category ?? '',
            `R$ ${Number(p.price).toFixed(2).replace('.',',')}`,
            String(p.min_qty ?? 0),
            String(p.stock_qty ?? 0)
          ]);
          doc.autoTable({
            head,
            body,
            startY: 60,
            styles: { fontSize: 10, overflow: 'linebreak' },
            headStyles: { fillColor: [14,165,233] },
            bodyStyles: { cellPadding: 4 }
          });
          doc.save('estoque.pdf');
        } catch (err) {
          console.error(err);
          alert('Não foi possível gerar o PDF. Tente novamente e confira sua internet.');
        }
      }

      // Sidebar navigation controlling visible section and title
      document.getElementById('side-menu').addEventListener('click', function(e){
        const link = e.target.closest('a[data-target]');
        if (!link) return;
        e.preventDefault();
        // activate link
        this.querySelectorAll('a').forEach(a => a.classList.remove('active'));
        link.classList.add('active');
        // show tab
        const target = link.getAttribute('data-target');
        document.querySelectorAll('.tab-pane').forEach(pane => {
          pane.classList.remove('show','active');
        });
        const pane = document.querySelector(target);
        if (pane) { pane.classList.add('show','active'); }
        // update title
        const title = link.textContent.trim();
        document.getElementById('section-title').textContent = title;
      });
    </script>
  </body>
</html>
