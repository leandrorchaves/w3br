<?php

/**
 * Module for manage doctrine ORM integration with W3br.
 * @package DoctrineModule
 * 
 * @author Leandro Chaves
 * @link http://leandrochaves.com
 */
class ExcelModule {

    public static function path() {
        return MODULES_PATH . "/ExcelModule";
    }

    public static function autoLoad($class) {
        include_once self::path() . '/PHPExcel.php';
        return PHPExcel_Autoloader::Load($class);
    }

    public static function start() {
//        ini_set('memory_limit','512M');
//        echo file_get_contents(self::path().'/PHPExcel/Shared/ZipStreamWrapper.php');

        require_once self::path() . '/PHPExcel/Cell/IValueBinder.php';
        require_once self::path() . '/PHPExcel/Shared/String.php';
        require_once self::path() . '/PHPExcel/Shared/ZipStreamWrapper.php';
        require_once self::path() . '/PHPExcel/Shared/Font.php';

        require_once self::path() . '/PHPExcel/Cell/DataType.php';
        require_once self::path() . '/PHPExcel/Cell/DefaultValueBinder.php';


        require_once self::path() . '/PHPExcel/Shared/File.php';
        require_once self::path() . '/PHPExcel/Shared/Date.php';
        require_once self::path() . '/PHPExcel/Writer/IWriter.php';

        require_once self::path() . '/PHPExcel/Writer/Excel5/BIFFwriter.php';

        require_once self::path() . '/PHPExcel/Writer/Excel5/Xf.php';

        require_once self::path() . '/PHPExcel/Writer/Excel5/Font.php';
        require_once self::path() . '/PHPExcel/Cell.php';
        require_once self::path() . '/PHPExcel/Shared/OLE.php';
        require_once self::path() . '/PHPExcel/Shared/OLE/PPS.php';

        require_once self::path() . '/PHPExcel/Shared/OLE/PPS/Root.php';
        require_once self::path() . '/PHPExcel/Shared/OLE/PPS/File.php';
        require_once self::path() . '/PHPExcel/Writer/Excel5/Worksheet.php';
        require_once self::path() . '/PHPExcel/Writer/Excel5/Parser.php';
        require_once self::path() . '/PHPExcel/Writer/Excel5/Workbook.php';
        require_once self::path() . '/PHPExcel/WorksheetIterator.php';
        require_once self::path() . '/PHPExcel/IComparable.php';
        require_once self::path() . '/PHPExcel/Worksheet/PageSetup.php';
        require_once self::path() . '/PHPExcel/Worksheet/PageMargins.php';
        require_once self::path() . '/PHPExcel/Calculation.php';
        require_once self::path() . '/PHPExcel/Calculation/Function.php';
        require_once self::path() . '/PHPExcel/Calculation/Functions.php';
        require_once self::path() . '/PHPExcel/Worksheet/HeaderFooter.php';
        require_once self::path() . '/PHPExcel/Worksheet/SheetView.php';
        require_once self::path() . '/PHPExcel/Worksheet/Protection.php';
        require_once self::path() . '/PHPExcel/Worksheet/RowDimension.php';
        require_once self::path() . '/PHPExcel/Worksheet/ColumnDimension.php';
        require_once self::path() . '/PHPExcel/DocumentProperties.php';
        require_once self::path() . '/PHPExcel/DocumentSecurity.php';
        require_once self::path() . '/PHPExcel/Style.php';
        require_once self::path() . '/PHPExcel/Style/Font.php';
        require_once self::path() . '/PHPExcel/Style/Color.php';
        require_once self::path() . '/PHPExcel/Style/Fill.php';
        require_once self::path() . '/PHPExcel/Style/Borders.php';
        require_once self::path() . '/PHPExcel/Style/Border.php';
        require_once self::path() . '/PHPExcel/Style/Alignment.php';
        require_once self::path() . '/PHPExcel/Style/NumberFormat.php';
        require_once self::path() . '/PHPExcel/Style/Protection.php';
        require_once self::path() . '/PHPExcel/IOFactory.php';
        require_once self::path() . '/PHPExcel/Writer/Excel5.php';
        require_once self::path() . '/PHPExcel/Worksheet.php';

        require_once self::path() . '/PHPExcel/ReferenceHelper.php';
        require_once self::path() . '/PHPExcel/CachedObjectStorage/CacheBase.php';
        require_once self::path() . '/PHPExcel/CachedObjectStorage/ICache.php';

        require_once self::path() . '/PHPExcel/CachedObjectStorage/Memory.php';

        require_once self::path() . '/PHPExcel/CachedObjectStorageFactory.php';



        include_once self::path() . '/PHPExcel.php';
        //PHPExcel_Autoloader::load('PHPExcel_Shared_ZipStreamWrapper'); 
    }

}
?>