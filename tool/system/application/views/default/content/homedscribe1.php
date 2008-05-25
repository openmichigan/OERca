<h1><?=$name . "'s"?> Status</h1><br />

  <?php if ($courses) {
    foreach ($courses as $key => $value) { 
       $params_url = $value['num']['total'].'/'.$value['num']['done']. 
              '/'.$value['num']['ask'].'/'.$value['num']['rem'];
   ?> 
  <div class="column span-16 first">
    <a href="<?php echo site_url("materials/home/{$value['id']}"); ?>" ><img src="<?= site_url("/home/make_bar/$params_url") ?>" 
    alt="Progress Bar: 
      Total Objects=<?=$value['num']['total'] ?>
      Cleared Objects=<?=$value['num']['done'] ?> 
      Objects in progress=<?=$value['num']['ask'] ?> 
      Remaining Objects=<?=$value['num']['rem'] ?>"
    > </a>
  </div>
  <div class="column span-8 last" >
    <br />
    <h2><a href="<?php echo site_url("materials/home/{$value['id']}"); ?>" >
          <?=$value['number'] ?> <?=$value['title'] ?>
        </a>
    </h2>
  </div>
  <?php }} else { ?>
  <div class="column span-24 first last">
    You have no courses at present. Ask one of the staff to assign a course.
  </div>
  <?php } ?>

