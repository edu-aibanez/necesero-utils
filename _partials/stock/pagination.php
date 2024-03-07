<?php

$paginate_by = [
  10,
  25,
  50,
  100,
  200,
  500,
  1000,
  10000,
];

?>

<div class="pagination-container">

  <div class="pagination-select" style="float:left">
    <!-- Selector de resultados por página -->
    <form  method="post" id="paginationForm">
      Mostrar
      <select name="resultados_por_pagina" onchange="paginar()">

        <!-- @todo hacer programático -->
        <?php foreach ($paginate_by as $value) : ?>
          <option value="<?php echo $value; ?>" <?php echo ($resultados_por_pagina == $value) ? 'selected' : ''; ?>><?php echo $value; ?></option>
        <?php endforeach; ?>

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
