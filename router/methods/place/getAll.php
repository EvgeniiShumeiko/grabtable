<?php
 add_controller('place');

 $core = CoreAPI::getCore();

 $fields = $core->getFields('GET', [], ['page']);

 $page = isset($fields['page']) && intval($fields['page']) > 0 ? intval($core['page']): 0;
 $count = 30;
 $offset = $page * $count;

 $places = get_all_place($offset, $count);

 $core->response($places);