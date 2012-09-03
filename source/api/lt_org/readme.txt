本文件夹中是ESM提供的接口主要方法都在esn.php文件中，
重要类说明

-----------------------------------|----------------|----------------参数-------------------------------------------|----返回值---------
listOrganization($name,$parentId)  | 查找部门的方法 | $name 部门名称，支持模糊查询 ；$parentId 上级部门id，匹配查询 | $GLOBAL["group"]
listStation($name,$code)           | 查找岗位的方法 | $name 岗位名称，支持模糊查询 ；$code  岗位编码，匹配查询      | $GLOBAL["station"]
loadDetail($obj,$id)               | 查找某个实体   | $obj 数据库表名 $id 实体的id                                  | $GLOBAL[$obj]
