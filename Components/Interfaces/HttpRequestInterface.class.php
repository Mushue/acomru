<?php


interface HttpRequestInterface
{
    public static function create();

    public static function createFromGlobals();

    public function &getGet();

    public function getGetVar($name);

    public function hasGetVar($name);

    public function setGet(array $get);

    public function setGetVar($name, $value);

    public function &getPost();

    public function getPostVar($name);

    public function hasPostVar($name);

    public function setPost(array $post);

    public function setPostVar($name, $value);

    public function &getServer();

    public function getServerVar($name);

    public function hasServerVar($name);

    public function setServer(array $server);

    public function setServerVar($name, $value);

    public function &getCookie();

    public function getCookieVar($name);

    public function hasCookieVar($name);

    public function setCookie(array $cookie);

    public function &getSession();

    public function getSessionVar($name);

    public function hasSessionVar($name);

    public function setFiles(array $files);

    public function &getFiles();

    public function getFilesVar($name);

    public function hasFilesVar($name);

    public function setSession(array &$session);

    public function setAttachedVar($name, $var);

    public function &getAttached();

    public function getAttachedVar($name);

    public function unsetAttachedVar($name);

    public function hasAttachedVar($name);

    public function getByType(RequestType $type);

    public function getHeaderList();

    public function hasHeaderVar($name);

    public function getHeaderVar($name);

    public function unsetHeaderVar($name);

    public function setHeaderVar($name, $var);

    public function setHeaders(array $headers);

    public function setMethod(HttpMethod $method);

    public function getMethod();

    public function setUrl(HttpUrl $url);

    public function getUrl();

    public function hasBody();

    public function getBody();

    public function setBody($body);
}