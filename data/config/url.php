<?php 
return array (
  'URL_MODEL' => 2,
  'URL_HTML_SUFFIX' => '.html',
  'URL_PATHINFO_DEPR' => '/',
  'URL_ROUTER_ON' => true,
  'URL_ROUTE_RULES' => 
  array (
    '/^book\/$/' => 'book/index',
    '/^book-p(\d+)$/' => 'book/index?p=:1',
    '/^c(\d+)\/$/' => 'book/cate?cid=:1',
    '/^c(\d+)\/p(\d+)$/' => 'book/cate?cid=:1&p=:2',
    '/^album\/$/' => 'album/index',
    '/^album\/p(\d+)$/' => 'album/index?p=:1',
    '/^album\/c(\d+)$/' => 'album/index?cid=:1',
    '/^album\/c(\d+)\/p(\d+)$/' => 'album/index?cid=:1&p=:2',
    '/^album\/(\d+)\/$/' => 'album/detail?id=:1',
    '/^item\/(\d+).html$/' => 'item/index?id=:1',
    '/^ec\/$/' => 'exchange/index',
    '/^ec\/p(\d+)$/' => 'exchange/index?p=:1',
    '/^space$/' => 'space/index',
    '/^space\/(\d+)$/' => 'space/index?uid=:1',
    '/^spaace\/(\d+)$/' => 'spaace/index?uid=:1',
	'/^property$/' => 'property/index',
    '/^property\/index$/' => 'property/index',
    '/^property\/h(\d+)\-t(\d+)\/$/' => 'property/index?house_type=:1&tenement=:2',
    '/^d$/' => 'download/index',
	'/^brand$/' => 'brand/index',
    '/^brand\/index$/' => 'brand/index',
	'/^joinus$/' => 'joinus/index',
    '/^joinus\/index$/' => 'joinus/index',
	'/^contact$/' => 'article/contact',
    '/^contact\/index$/' => 'article/contact',
	'/^about$/' => 'article/about',
    '/^about\/index$/' => 'article/about',
    '/^property\/a(\d+)\-h(\d+)\-t(\d+)\/$/' => 'property/index?area=:1&house_type=:2&tenement=:3',
    '/^property\/a(\d+)\-t(\d+)\/$/' => 'property/index?area=:1&tenement=:2',
    '/^property\/a(\d+)\-h(\d+)\/$/' => 'property/index?area=:1&house_type=:2',
    '/^property\/s\/(\d+).html$/' => 'property/detail?pid=:1',
    '/^property\/p(\d+)$/' => 'property/index?p=:1',
    '/^brand\/e_(\w+)$/' => 'brand/index?eg=:1',
    '/^brand\/p(\d+)$/' => 'brand/index?p=:1',
    '/^brand\/s\/(\d+).html$/' => 'brand/detailed?id=:1',
	'/^test\/$/' => 'test/index',

  ),
);