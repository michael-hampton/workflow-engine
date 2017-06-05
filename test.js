$arrFields = array(
    0 => "mike",
    1 => "uan",
    2 => "lexi",
    3 => "paul",
    4 => "bish",
    5 => "Jamie",
    6 => "mike",
    7 => "uan",
    8 => "lexi",
    9 => "paul",
    10 => "bish",
    11 => "bish"
);

$rows = count($arrFields);    // total no of fields
if($rows > 0) {
    $cols = 1;    // Define number of columns

    $colCount = ceil(12 / $cols);

    $counter = 1;     // Counter used to identify if we need to start or end a row
    $nbsp = $cols - ($rows % $cols);    // Calculate the number of blank columns

    $container_class = 'container-fluid';  // Parent container class name
    $row_class = 'row';    // Row class name
    $col_class = 'col-sm-'.$colCount; // Column class name

    echo '<div class="'.$container_class.'">';    // Container open
    foreach($arrFields as $strField) {
        if(($counter % $cols) == 1 || $cols === 1) {    // Check if it's new row
            echo '<div class="'.$row_class.'">';	// Start a new row
        }

        echo '<div class="'.$col_class.'">
            <div class="form-group">
                <label class="col-lg-2 control-label">'.$strField.'</label>

                <div class="col-lg-8">
                    <input type="text" placeholder="Email" class="form-control">
                </div>
            </div>

        </div>';     // Column with content
        if(($counter % $cols) == 0) { // If it's last column in each row then counter remainder will be zero
            echo '</div>';	 //  Close the row
        }
        $counter++;    // Increase the counter
    }
    //$result->free();
    if($nbsp > 0) { // Adjustment to add unused column in last row if they exist
        for ($i = 0; $i < $nbsp; $i++)	{
            echo '<div class="'.$col_class.'">&nbsp;</div>';
        }
        echo '</div>';  // Close the row
    }
    echo '</div>';  // Close the container
}
?>
