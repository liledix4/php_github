<?php
require_once __DIR__.'/../php_config/config.php';

config::setFromFile();

final class GitHub
{
    final public static function rawFile
    (
        string  $remoteFilePath,
        string  $user = null,
        string  $repo = null,
        string  $branch = null,
        bool    $writeToFile = false,
        string  $saveToFile = null
    ): void
    {
        $config = config::$config;
        $token = null;
        if (isset($config['github']['token']))
            $token = $config['github']['token'];

        if ($user   === null) $user    = $config['github']['defaults']['user'];
        if ($repo   === null) $repo    = $config['github']['defaults']['repo'];
        if ($branch === null) $branch  = $config['github']['defaults']['branch'];

        $ch = curl_init(url: "https://raw.githubusercontent.com/$user/$repo/refs/heads/$branch/$remoteFilePath");

        $fileOperations = false;
        if ($writeToFile === true && $saveToFile !== null)
            $fileOperations = true;

        if ($fileOperations === true)
        {
            $fp = fopen(filename: $saveToFile, mode: "w");
            curl_setopt(handle: $ch, option: CURLOPT_FILE, value: $fp);
        }

        if (is_string(value: $token))
            curl_setopt
            (
                handle: $ch, option: CURLOPT_HTTPHEADER,
                value: ["Authorization: token $token"]
            );

        curl_exec(handle: $ch);

        if ($fileOperations === true)
        {
            if (curl_error(handle: $ch))
                fwrite(stream: $fp, data: curl_error(handle: $ch));
            fclose(stream: $fp);
        }

        curl_close(handle: $ch);
    }
}