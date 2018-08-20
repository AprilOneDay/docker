# 创建用户
INSERT INTO mysql.user(HOST,USER,authentication_string) VALUES("%","beta",PASSWORD("lvgVcNFvEk"));
# 为用户授权
GRANT ALL PRIVILEGES ON kljgj_beta.* TO "beta"@"%" IDENTIFIED BY 'lvgVcNFvEk';
# 删除用户
DROP USER beta@localhost;
# 刷新权限
FLUSH PRIVILEGES;
# 查询用户表
SELECT HOST,USER,authentication_string FROM mysql.user
# 查询当前用户
SELECT USER();