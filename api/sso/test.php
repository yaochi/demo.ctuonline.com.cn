<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>测试</title>
    </head>

    <body>
        <h2>组织 </h2>
        <?php
        $orgid = 15790;
        $v = 1;
        $method = "edit";
        $orgrinfo = "";
        $nowtime = "";
        $code = md5($orgid.$method.$v.$nowtime);
        ?>
        <form action="organization.php" method="post">
            <input type="hidden" name="v" value="<?php echo $v;?>"/>
            <table>
                <tr><td>orgid</td><td><input type="text" name="orgid" value="<?php echo $orgid; ?>" readonly="true"/></td></tr>
                <tr><td>method</td><td><input type="text" name="method" value="<?php echo $method; ?>" readonly="true"/></td></tr>
                <tr><td>orgrinfo</td><td><input type="text" name="orgrinfo" value="<?php echo $orgrinfo; ?>" readonly="true"/></td></tr>
                <tr><td>nowtime</td><td><input type="text" name="nowtime" value="<?php echo $nowtime; ?>" readonly="true"/></td></tr>
                <tr><td>md5</td><td><input type="text" name="securecode" value="<?php echo $code; ?>" readonly="true"/></td></tr>
                <tr><td colspan="2"><input type="submit" name="submit" value="提交" /></td></tr>
            </table>
        </form>


        <h2>组织关系</h2>
         <?php
        $oldorgid = 15790;
        $v = 1;
        $uid = 54805;
        $neworgid = 0;//19350;
        $nowtime = "";
        $code = md5($uid.$oldorgid.$neworgid.$v.$nowtime);
        ?>
        <form action="orguser.php" method="post">
            <input type="hidden" name="v" value="<?php echo $v;?>"/>
            <table>
                <tr><td>oldorgid</td><td><input type="text" name="oldorgid" value="<?php echo $oldorgid; ?>" readonly="true"/></td></tr>
                <tr><td>uid</td><td><input type="text" name="uid" value="<?php echo $uid; ?>" readonly="true"/></td></tr>
                <tr><td>neworgid</td><td><input type="text" name="neworgid" value="<?php echo $neworgid; ?>" readonly="true"/></td></tr>
                <tr><td>nowtime</td><td><input type="text" name="nowtime" value="<?php echo $nowtime; ?>" readonly="true"/></td></tr>
                <tr><td>md5</td><td><input type="text" name="securecode" value="<?php echo $code; ?>" readonly="true"/></td></tr>
                <tr><td colspan="2"><input type="submit" name="submit" value="提交" /></td></tr>
            </table>
        </form>
        
        <h2>用户</h2>
         <?php
        $uid = 54805;
        $nowtime = "";
        $method = "delete";
        $v = 1;
        $code = md5($uid.$method.$v.$nowtime);
        ?>
        <form action="user.php" method="post">
            <input type="hidden" name="v" value="<?php echo $v;?>"/>
            <table>
                <tr><td>uid</td><td><input type="text" name="uid" value="<?php echo $uid; ?>" readonly="true"/></td></tr>
                <tr><td>method</td><td><input type="text" name="method" value="<?php echo $method; ?>" readonly="true"/></td></tr>
                <tr><td>userinfo</td><td><input type="text" name="userinfo"/></td></tr>
                <tr><td>nowtime</td><td><input type="text" name="nowtime" value="<?php echo $nowtime; ?>" readonly="true"/></td></tr>
                <tr><td>md5</td><td><input type="text" name="securecode" value="<?php echo $code; ?>" readonly="true"/></td></tr>
                <tr><td colspan="2"><input type="submit" name="submit" value="提交" /></td></tr>
            </table>
        </form>
    </body>
</html>