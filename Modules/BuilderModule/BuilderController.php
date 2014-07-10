<?php

/**
 * Encoda os scripts para melhoria de performance.
 *
 * @author Leandro Chaves<leandro.chaves@h2asol.com>
 * 
 */
class BuilderController {

    const TITLE = 'W3BR.bcompiler - Encode your PHP script using bcompiler';

    private $verbose, $basename, $follow_symlink, $copy_rest;
    private $suffix, $recursive, $force_overwrite, $compress, $config;

    public function buildAction() {
        $this->show_log("Iniciando deploy...");
        $this->loadConfig();
        $this->generateJS();
        $this->copy(Array('-s', '.', '-o', './build', '-f', '-r', '-c', '-q'));
        $this->send();
    }

    private function loadConfig() {
        $this->config = json_decode(file_get_contents(DIR . 'config/build.json'));
    }

    public function send() {
        $this->show_log("Enviando Arquivos...");
        $command = 'rsync -arvuz ./build/ ';

//        $this->config->remote->dir = '/var/www/dev';
//        $this->config->remote->access = 'leandro@srv6.h2a-saas.com';
//        $chmod = Array(
//            'unisys',
//            'www/Smarty',
//            'h/Smarty',
//            'arquivos',
//            'logs'
//        );

        shell_exec($command . $this->config->remote->access . ':' . $this->config->remote->dir);
        foreach ($this->config->chmod as $dir) {
            shell_exec('ssh ' . $this->config->remote->access . ' \'chmod -Rf 777 ' . $this->config->remote->dir . '/' . $dir . '\'');
        }
        $this->show_log("Arquivos Enviados com sucesso");
        // ssh leandro@srv6.h2a-saas.com 'chmod -Rf 777
    }

    /**
     * Roda o generate.py do Qooxdoo para gerar os arquivos javascript.
     */
    public function generateJS() {
        $this->show_log("Gerando JS...");
        $exec = 'generate.py';
        $dir = DIR . 'a';
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..')
                continue;
            $path = "$dir/$file";
            if (file_exists($path . '/' . $exec)) {
                chdir($path);
                shell_exec("python {$exec} build");
            }
        }
        chdir(DIR);
    }

    public function copy($params) {
        $this->show_log("Copiando arquivos...");
        // Verifica se o bcompiler está habilitado
//        if (!function_exists('bcompiler_write_file')) {
//            print self::TITLE . "%s\n\n";
//            print "ERROR: Please install `bcompiler' before running this encoder\n\n";
//            print "  cmd> pecl install bcompiler\n\n";
//            print "You may also need `libbz2-dev' package to compile bcompiler\n\n";
//            exit;
//        }

        $this->suffix = 'php|html|htm';
        $output = $srcdir = '';
        $infiles = array();
        $this->verbose = true;
        $this->compress = false;
        $this->follow_symlink = $this->force_overwrite = $this->copy_rest = $this->recursive = false;
        $this->basename = false;

        for ($i = 0; $i < count($params); $i++) {
            switch ($params[$i]) {
                case '-h':
                    self::print_usage();
                    break;

                case '-v':
                    print self::TITLE . "\n";
                    exit(0);
                    break;

                case '-b':
                case '-bz2':
                    if (!function_exists('bzopen')) {
                        self::error('bzip2 extension is not installed');
                    }
                    $this->compress = true;
                    break;

                case '-f':
                    $this->force_overwrite = true;
                    break;

                case '-o':
                    if (++$i < count($params))
                        $output = $params[$i];
                    break;

                case '-s':
                case '-a':
                    if (++$i < count($params))
                        $srcdir = $params[$i];
                    break;

                case '-e':
                    if (++$i < count($params))
                        $this->suffix = $params[$i];
                    break;

                case '-c':
                    $this->copy_rest = true;
                    break;

                case '-r':
                    $this->recursive = true;
                    break;

                case '-l':
                    $this->follow_symlink = true;
                    break;

                case '-q':
                    $this->verbose = false;
                    break;

                case '-t':
                    if (!function_exists('bcompiler_set_filename_handler')) {
                        self::error('please upgrade your bcompiler extension to support -t option');
                    }
                    $this->basename = true;
                    break;

                default:
                    $match = Array();
                    if (preg_match('/^-o(.+)$/', $params[$i], $match)) {
                        $output = $match[1];
                    } else {
                        $infiles[] = $params[$i];
                    }
            }
        }

        $numfiles = count($infiles);

        $outdir = '';
        if ($numfiles > 0 || $srcdir != '')
            $outdir = $output;

        if ($srcdir == '') {
            if ($numfiles == 0)
                self::print_usage();
            if ($numfiles > 1 && $outdir == '')
                self::error("You should use `-o DIR' to specify the output directory");
        } else {
            if ($numfiles > 0)
                self::error("You can not encode files and specify `-s DIR' at the same times");
            if ($outdir == '')
                self::error("You should use `-o DIR' to specify the output directory");
        }

        if ($outdir != '') {
            if (file_exists($outdir)) {
                if (!is_dir($outdir))
                    self::error("[$outdir] already exists and is not a directory");
                if (!$this->force_overwrite)
                    self::error("The directory [$outdir] already exists, use -f option to force overwriting");
            }
            elseif ($numfiles > 1 || $srcdir != '')
                mkdir($output, 0755);
        }

        // Se for expecificado um diretorio de leitura
        if ($srcdir != '') {
            $this->process_dir($srcdir, $output);
        } elseif ($numfiles == 1) {
            if ($output == '') {
                $ext = substr(strrchr($infiles[0], '.'), 1);
                if ($ext)
                    $output = basename($infiles[0], ".$ext") . "-encoded.$ext";
                else
                    $output = $infiles[0] . '-encoded';
            }
            elseif (is_dir($output))
                $output = $outdir . '/' . $infiles[0];
            if (file_exists($output) && !$this->force_overwrite)
                bencoder_error("The file [$output] already exists, use -f option to force overwriting");
            if ($this->verbose)
                print self::TITLE . "\n\n";
            $this->encode_file($infiles[0], $output);
        }
        else {
            if ($this->verbose)
                print self::TITLE . "\n\n";
            foreach ($infiles as $infile) {
                $this->encode_file($infile, "$outdir/$infile");
            }
        }
    }

    /**
     * Verifica se um arquivo/pasta deve ser ignorado.
     * @param String $src path do arquivo a ser analizado
     */
    private function is_ignored($src) {
        $ignoreds = $this->get_ignoreds();
        if ($ignoreds) {
            $path = str_replace(DIR, '', realpath($src));
            foreach ($ignoreds as $regex) {
                if (preg_match("/^{$regex}$/", $path)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function get_ignoreds() {
        return $this->config->ignore;
    }

    private function process_dir($srcdir, $outdir) {
        $dh = opendir($srcdir);
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..')
                continue;
            $srcpath = "$srcdir/$file";
            $outpath = "$outdir/$file";

            // Verifica se o arquivo deve ser ignorado
            if (!$this->is_ignored($srcpath)) {

                // Verifica se é um link simbólico
                if (is_link($srcpath)) {
                    $real = readlink($srcpath);
                    $realpath = ($real[0] == '/') ? $real : "$srcdir/$real";
                    if ($this->follow_symlink) {
                        $srcpath = $realpath;
                    } else {
                        // Se o modo recursivo estiver habilitado
                        // Entra em todos os diretórios
                        if ($this->recursive || (!is_dir($realpath) && $this->copy_rest)) {
                            if (file_exists($outpath) && $this->force_overwrite)
                                @unlink($outpath);
                            symlink($real, $outpath);
                            $this->show_verbose("symlink: $outpath");
                        }
                        else
                            $this->show_verbose("skipped: $outpath");
                        continue;
                    }
                }
                // Verifica se é diretório
                if (is_dir($srcpath)) {
                    // Se o modo recursivo estiver habilitado
                    // Entra em todos os diretórios
                    if ($this->recursive) {
                        if (!file_exists($outpath)) {
                            if (mkdir($outpath, 0755)) {
                                $this->show_verbose("  mkdir: $outpath");
                            } else {
                                $this->show_verbose("  mkdir: $outpath (failed)");
                            }
                        } elseif (!is_dir($outpath)) {
                            self::error("$outpath is not a directory");
                        }
                        $this->process_dir($srcpath, $outpath);
                    }
                } elseif (is_readable($srcpath)) {
                    if (file_exists($outpath) && !$this->force_overwrite)
                        $this->show_verbose("skipped: $outpath");

                    // Verifica se é um arquivo php
                    elseif (preg_match("/\.($this->suffix)\$/", $file))
                        $this->encode_file($srcpath, $outpath);

                    // Se a opção copiar estiver habilitada
                    // Copia os demais arquivos
                    elseif ($this->copy_rest) {
                        if (@copy($srcpath, $outpath))
                            $this->show_verbose(" copied: $outpath");
                        else
                            print "   copy: $outpath (failed)\n";
                    }
                    continue;
                }
                else
                    print " failed: $outpath (un-readable file)\n";
            }
        }
        closedir($dh);
    }

    private function encode_file($infile, $outfile) {
//        $fh = @fopen($outfile, 'w');
//        if (!$fh) {
//            $this->show_verbose(" failed: $outfile (un-writable dir)");
//            return(0);
//        }
//        if ($this->basename) {
//            bcompiler_set_filename_handler('basename');
//        }
//        bcompiler_write_header($fh);
//        bcompiler_write_file($fh, $infile);
//        bcompiler_write_footer($fh);
//        fclose($fh);
        copy($infile, $outfile);
        if ($this->compress) {
            $content = file_get_contents($outfile);
            $bzfh = bzopen($outfile, "w");
            bzwrite($bzfh, $content);
            bzclose($bzfh);
            $this->show_verbose("encoded: $outfile (compressed)");
        }
        else
            $this->show_verbose("encoded: $outfile");

        return(1);
    }

    private static function error($err) {
        print self::TITLE . "\n\n";
        print "ERROR: $err\n\n";
        exit(1);
    }

    /**
     * Imprime o manual de uso.
     * 
     */
    private static function print_usage() {
        print self::help();
        die;
    }

    private function show_verbose($msg) {
        if ($this->verbose) {
            print "$msg\n";
        }
    }

    private function show_log($msg) {
        print "$msg\n";
    }

    private static function help() {
        $title = self::TITLE;

        $help = <<<HELP
$title

Usage: bencoder [-f] [-t] [-q] -o FILE    file1.php
       bencoder [-f] [-t] [-q] -o OUTDIR  file1.php file2.php ...
       bencoder [-f] [-t] [-q] -o OUTDIR  -s SRCDIR  [-e SUFFIX] [-r] [-c] [-l]

  -o FILE   : the file name to write the encoded script
              (default to '-encoded.XXX' suffix)
  -o OUTDIR : the directory to write all encoded files

  -s SRCDIR
  -a SRCDIR : encode all files from this source directory

  -r        : encode directories recursively (no by default)
  -f        : force overwriting even if the target exists
  -t        : truncate/keep only the basename of the file into the bytecode
  -e SUFFIX : encode the files with the SUFFIX extension only (default: php)
              (regular expression allowed, ex: "php|inc")
  -c        : copy files those shouldn't be encoded (no by default)
  -l        : follow symbolic link (no by default)
  -q        : do not print the file name while encoding or copying
  -b
  -bz2      : compress the encoded files with bz2 (needs bzip2-extension)

HELP;
        return $help;
    }

}

?>
