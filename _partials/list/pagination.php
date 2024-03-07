<div class="pagination-container">

  <div class="pagination-select" style="float:left">
    <!-- Selector de resultados por página -->
    <form  method="post" id="paginationForm">
      Mostrar
      <select name="resultados_por_pagina" onchange="paginar()">

        <!-- TODO hacer programático -->
        <option value="10" <?php echo ($resultados_por_pagina == 10) ? 'selected' : ''; ?>>10</option>
        <option value="25" <?php echo ($resultados_por_pagina == 25) ? 'selected' : ''; ?>>25</option>
        <option value="50" <?php echo ($resultados_por_pagina == 50) ? 'selected' : ''; ?>>50</option>
        <option value="100" <?php echo ($resultados_por_pagina == 100) ? 'selected' : ''; ?>>100</option>
      </select>
       de <?php echo $total_registros; ?> resultados
    </form>
  </div>

  <div class="pagination">
    <?php if ($pagina_actual > 1) : ?>
      <a href="?pagina=1"><i class="fa-solid fa-angles-left"></i></a>
      <a href="?pagina=<?php echo $pagina_actual - 1; ?>"><i class="fa-solid fa-angle-left"></i></a>
    <?php endif; ?>

    <?php for ($i = max(1, $pagina_actual - 2); $i <= min($total_paginas, $pagina_actual + 2); $i++) : ?>
      <a href="?pagina=<?php echo $i; ?>" <?php echo ($i == $pagina_actual) ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
    <?php endfor; ?>

    <?php if ($pagina_actual < $total_paginas) : ?>
      <a href="?pagina=<?php echo $pagina_actual + 1; ?>"><i class="fa-solid fa-angle-right"></i></a>
      <a href="?pagina=<?php echo $total_paginas; ?>"><i class="fa-solid fa-angles-right"></i></a>
    <?php endif; ?>
  </div>
</div>
<script>
    function paginar() {
        // Obtener el formulario
        var formulario = document.getElementById("paginationForm");

        // Realizar el submit con campos vacíos
        formulario.submit();
    }
</script>
