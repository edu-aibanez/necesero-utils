<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Verificar que se haya seleccionado un archivo
  if (isset($_FILES['ventas']) && $_FILES['ventas']['error'] === UPLOAD_ERR_OK) {
    // Obtener la ruta temporal del archivo
    $tmpFile = $_FILES['ventas']['tmp_name'];

    // Cargar la hoja de cálculo con PHPExcel
    require 'PHPExcel/PHPExcel.php';
    require 'tcpdf/tcpdf.php';

    $excel = PHPExcel_IOFactory::load($tmpFile);
    $sheet = $excel->getActiveSheet();

    /* ****************** */

    // Obtener los valores del formulario
    $laboratorio = $_POST['laboratorio'];
    $fechaInicio = $_POST['fechaInicio'];
    $fechaFin = $_POST['fechaFin'];
    $idInicio = $_POST['idInicio'];
    $idFin = $_POST['idFin'];
    $pdf = isset($_POST['pdf']) ? true : false;

    // Calcular el número de días entre las fechas
    $fechaInicio = new DateTime($fechaInicio);
    $fechaFin = new DateTime($fechaFin);

    /* ****************** */

    // Crear un nuevo array para almacenar las filas modificadas
    $sellout = array();

    // Agregar la fila de encabezado manualmente
    $headerRow = array(
      'Pedido',
      'CN',
      'Producto',
      'Uds',
      'Cód. postal',
      'Fecha',
      'Laboratorio'
    );

    /*
    Ya no es necesario porque los id's se definen manualmente
    // Leer el contenido del archivo ".lastorder" y almacenarlo en la variable "lastOrderId"
    $lastOrderId = file_get_contents('.lastorder');
    */


    // Comprobar que el número de id's es mayor que la cantidad total de productos del excel
    $columna = 'C';
    $ultimaFila = $sheet->getHighestRow();
    $sumaCant = 0;
    $sumaId = $idFin - $idInicio;

    // Obtenemos el sumatorio de cantidades
    for ($fila = 2; $fila <= $ultimaFila; $fila++) {
      $valorCelda = $sheet->getCell($columna . $fila)->getValue();
      is_numeric($valorCelda) ? $sumaCant += $valorCelda : $sumaCant += 0;
    }

    // Si la suma de cantidades es igual o mayor a la suma de ids, se detiene el programa
    if ($sumaCant > $sumaId) {
      echo "Error: La suma de cantidades ($sumaCant) supera la suma de ids entre $idInicio $idFin ($sumaId)";
      exit;
    }


    // Leer el contenido del archivo de códigos postales "cps.csv" y almacenarlo en el array "cps"
    $cps = array();
    if (($handle = fopen('cps.csv', 'r')) !== false) {
      while (($data = fgetcsv($handle)) !== false) {
        $cps[] = $data[0];
      }
      fclose($handle);
    }

    // Iniciamos el iterador y preparamos el salto de la primera línea
    $rowIterator = $sheet->getRowIterator();
    $primera = true;

    // Recorrer cada fila de la hoja de cálculo
    foreach ($rowIterator as $row) {

      // Si es la primera iteración saltamos
      if ($primera) {
        $primera = false;
        continue;
      }

      // Obtener los valores de las celdas
      $prodCod = $sheet->getCell('A' . $row->getRowIndex())->getValue();
      $prodDenom = $sheet->getCell('B' . $row->getRowIndex())->getValue();
      $prodQty = $sheet->getCell('C' . $row->getRowIndex())->getValue();

      // test tipos de datos
      /* var_dump();
      echo "\n";
      var_dump(is_float($prodQty));
      exit; */

      // Comprueba si el cod de producto y la cantidad son números
      if (!is_float($prodCod) && !is_float($prodQty)) {
        echo 'Error: Revisa los tipos de dato de la columna de código y cantidad. Deben ser de tipo numérico';
        echo '<br>';
        echo '<br>';
        echo 'El tipo del código de producto es: ' . gettype($prodCod) . '(Debe ser float)';
        echo '<br>';
        echo 'El tipo de la cantidad de producto es: ' . gettype($prodCod) . '(Debe ser float)';
        exit;
      }

      // Mientras que el valor de prodQty sea mayor que cero
      while ($prodQty > 0) {
        // Crear un array vacío para la fila
        $rowArray = array();

        // Insertar los valores en el array row
        $rowArray[] = $prodCod;
        $rowArray[] = $prodDenom;

        // Generar un número aleatorio de unidades (uds)
        if ($prodQty >= 4) {

          // Saco un valor del 0 al 9
          $udAux = mt_rand(0, 9);

          //TO-DO: Más prioridad a cantidades bajas (i.e. 1-2)
          if ($udAux >= 0 && $udAux <= 5) {
            $uds = 1;
          } elseif ($udAux >= 6 && $udAux <= 7) {
            $uds = mt_rand(1, 2);
          } elseif ($udAux == 8) {
            $uds = mt_rand(1, 3);
          } elseif ($udAux == 9) {
            $uds = mt_rand(1, 4);
          }
        } elseif ($prodQty >= 2) {
          $uds = mt_rand(1, 2);
        } else {
          $uds = mt_rand(1, $prodQty);
        }

        // Insertar el valor de uds en el array row
        $rowArray[] = $uds;

        // Obtener un elemento aleatorio del array cps
        $cp = $cps[array_rand($cps)];

        // Restar uds a prodQty
        $prodQty -= $uds;

        // Insertar el valor de cp en el array row (después de uds)
        $rowArray[] = $cp;

        // Insertar la fila en el array sellout
        $sellout[] = $rowArray;
      }
    }
    // Mezclamos los elementos del array sellout
    shuffle($sellout);

    /* ****** Crear relación de id's ****** */

    // Crear el array de id's
    $ids = array();

    for ($i = 0; $i < count($sellout); $i++) {

      // TO-DO: Hacer que se repitan los ids eventualmente
      /* $probabilidad = 1 / 300;
      $random = mt_rand() / mt_getrandmax();

      // Una de cada 300 veces se repetirá un id
      if ($random < $probabilidad) {
        $ids[] = 0;
        continue;
      } */

      // Buscamos un id válido que no exista en el array de id's
      do {
        $idAux = mt_rand($idInicio, $idFin);
      } while (in_array($idAux, $ids));

      // Almacenamos el id en el array de id's
      $ids[] = $idAux;
    }

    // Ordenar el array de ids de manera ascendente
    sort($ids);

    /* ****** FIN Crear relación de id's ****** */


    /* ****** Crear relación de fechas ****** */

    // Crear el array de fechas
    $fechas = array();
    $fechaPuntero = clone $fechaInicio;

    for ($i = 0; $i < count($sellout); $i++) {

      $fechaAux = clone $fechaPuntero;

      // Si la fecha va a superar el valor de fechaFin
      if ($fechaAux->add(new DateInterval('P3D')) >= $fechaFin) {

        // 1 de cada 4 veces generamos una fecha cercana al límite
        $ult = mt_rand(0, 3);
        if ($ult == 0) {
          $ultFecha = clone $fechaFin;
          $lopt = mt_rand(0, 3);
          if ($lopt == 0) {
            $fechas[] = $ultFecha->format('Y-m-d');
          } elseif ($lopt == 1) {
            $ultFecha->sub(new DateInterval('P1D'));
            $fechas[] = $ultFecha->format('Y-m-d');
          } elseif ($lopt == 2) {
            $ultFecha->sub(new DateInterval('P2D'));
            $fechas[] = $ultFecha->format('Y-m-d');
          } elseif ($lopt == 3) {
            $ultFecha->sub(new DateInterval('P3D'));
            $fechas[] = $ultFecha->format('Y-m-d');
          }
          continue;
        } else {
          // El resto de veces reiniciamos el valor de fechaPuntero
          $fechaPuntero = clone $fechaInicio;
        }
      }

      $opt = mt_rand(0, 9);

      if ($opt >= 0 && $opt <= 3) {
        $fechas[] = $fechaPuntero->format('Y-m-d');
      } elseif ($opt >= 4 && $opt <= 6) {
        $fechaPuntero->add(new DateInterval('P1D'));
        $fechas[] = $fechaPuntero->format('Y-m-d');
      } elseif ($opt >= 7 && $opt <= 8) {
        $fechaPuntero->add(new DateInterval('P2D'));
        $fechas[] = $fechaPuntero->format('Y-m-d');
      } elseif ($opt == 9) {
        $fechaPuntero->add(new DateInterval('P3D'));
        $fechas[] = $fechaPuntero->format('Y-m-d');
      }
    }

    // Ordenar el array de fechas de manera ascendente
    sort($fechas);

    /* ****** FIN Crear relación de fechas ****** */

    /* ****** Insertar ids, fechas y laboratorio ****** */

    // Recorrer el array sellout
    for ($i = 0; $i < count($sellout); $i++) {
      array_unshift($sellout[$i], $ids[$i]);
      $sellout[$i][] = $fechas[$i];
      $sellout[$i][] = $laboratorio;
    }

    /* ****** FIN Insertar fechas y laboratorio ****** */

    // Crear un archivo CSV
    $randomFilename = mt_rand(1000000000, 9999999999);
    $fp = fopen($randomFilename . '.csv', 'w');

    // Agregar la fila de encabezado al archivo CSV
    fputcsv($fp, $headerRow);

    // Agregar el contenido del array sellout al archivo CSV
    foreach ($sellout as $row) {
      fputcsv($fp, $row);
    }

    fclose($fp);

    /*
    Ya no es necesario porque los id's se definen manualmente
    // Actualizar el contenido del archivo ".lastorder" con el valor de lastOrderId
    file_put_contents('.lastorder', $lastOrderId);
    */

    if ($pdf) {
      // Ruta del archivo CSV
      $csvFile = __DIR__ . '/' . $randomFilename . '.csv';

      // Nombre del archivo PDF de salida
      $pdfFile = __DIR__ . '/' . $randomFilename . '.pdf';

      // Ruta de la imagen para la cabecera
      $imageFile = __DIR__ . '/../../assets/img/logo.png';

      // Dimensiones de la imagen de cabecera (en milímetros)
      $imageWidth = 50;  // Ancho de la imagen
      $imageHeight = 8;  // Alto de la imagen

      // Lee el archivo CSV y conviértelo en un arreglo
      $data = [];
      if (($handle = fopen($csvFile, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
          $data[] = $row;
        }
        fclose($handle);
      }

      // Crea el objeto TCPDF
      $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

      // Agrega una página
      $pdf->AddPage();

      // Define el tamaño de fuente y los márgenes
      $pdf->SetFont('Helvetica', '', 10);
      $pdf->SetMargins(10, 10, 10);
      // Ajusta el margen inferior para el número de página
      $pdf->SetFooterMargin(10);

      // Agrega la cabecera
      $pdf->Image($imageFile, 10, 10, $imageWidth, $imageHeight, 'PNG');

      // Obtén la posición x de la imagen para calcular la posición x del texto
      $imageX = $pdf->GetX();
      $imageWidthMM = $pdf->pixelsToUnits($imageWidth);
      $textX = $imageX + $imageWidthMM + 5;

      // Agrega el texto de la cabecera alineado a la derecha
      $pdf->SetX($textX);
      $pdf->Cell(0, 10, 'Badasalud, S.L. - Contacto: administracion@masparafarmacia.com', 0, 1, 'R');
      // $pdf->Cell(0, 10, 'MasParafarmacia - Badasalud, S.L. - Contacto: administracion@masparafarmacia.com', 0, 1, 'C');

      // Calcula el ancho de las columnas
      $totalColumns = count($data[0]);
      $columnWidths = array();
      $baseWidth = ($pdf->GetPageWidth() - 20) / $totalColumns;

      // Definir los factores de ajuste para cada columna
      $adjustFactors = array(0.5, 0.5, 2, 0.5, 0.75, 1, 0.75);

      // Calcular los anchos de las columnas según los factores de ajuste
      for ($i = 0; $i < $totalColumns; $i++) {
        $columnWidths[$i] = $baseWidth * $adjustFactors[$i];
      }

      // Calcula el ancho total de la tabla
      $tableWidth = array_sum($columnWidths);

      // Calcula el desplazamiento necesario para centrar la tabla
      $tableOffset = ($pdf->GetPageWidth() - $tableWidth) / 2;

      // Establece la posición x actual para centrar la tabla
      $pdf->SetX($tableOffset);

      // Recorre los datos y genera el contenido del PDF
      foreach ($data as $row) {
        $pdf->SetX($tableOffset); // Restablece la posición x en cada fila

        for ($i = 0; $i < $totalColumns; $i++) {
          $cellWidth = $columnWidths[$i];
          $pdf->Cell($cellWidth, 10, $row[$i], 1, 0, 'C');
        }

        $pdf->Ln();
      }

      // Genera el archivo PDF
      $pdf->Output($pdfFile, 'F');

      // Ruta y nombre de archivo ZIP
      $zipFilename = $randomFilename . '.zip';

      // Crear objeto ZipArchive
      $zip = new ZipArchive();

      // Crear archivo ZIP
      if ($zip->open($zipFilename, ZipArchive::CREATE) === true) {
        // Agregar el archivo CSV al ZIP
        $zip->addFile($csvFile, $randomFilename . '.csv');

        // Agregar el archivo PDF al ZIP
        $zip->addFile($pdfFile, $randomFilename . '.pdf');

        // Cerrar el archivo ZIP
        $zip->close();

        // Descargar el archivo ZIP
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($zipFilename));
        readfile($zipFilename);

        // Eliminar el archivo ZIP después de la descarga
        unlink($zipFilename);

        // Eliminar el archivo CSV y el PDF
        unlink($randomFilename . '.csv');
        unlink($randomFilename . '.pdf');
      }
    } else {
      // Descargar el archivo CSV
      header('Content-Type: application/csv');
      header('Content-Disposition: attachment; filename="' . $randomFilename . '.csv"');
      readfile($randomFilename . '.csv');

      // Eliminar el archivo CSV
      unlink($randomFilename . '.csv');
    }
    exit;
  }
}
