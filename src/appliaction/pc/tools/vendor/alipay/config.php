<?php
$config = array(
    //应用ID,您的APPID。
    'app_id'               => "2016080600182200",

    //商户私钥
    'merchant_private_key' => "-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAwDYEA8TL35VHsE80RLrvAIPLU2/XbbibUThLYvd5xZGEkR+B
fWyciUzDj1qsCaOe6PJmvgoAmpLqRJU6VZTfNqJ89D/tUqKDzeLGTAEB2o3sCPTn
FQS76nrOgmznVlhlWaRhtcR0O+NTO5JPGNzd8u34BkCQLBFNDUvRbgr9XRF1UYwY
d3vDK6hdfAL8pjg4pv8/OwGhnSKs0xZzs7onJSh5jnvqiS8fZ3EB2b5jzuP6iyBw
QVfP+bhWeA3ej+sWSn3jclbF9FAv1TnZjSDsccqCRzwCIQ7KtgIQFa0/wHygDDQF
Ey6KBXB3bFiqWRUSxRKEhS/gJBMrCJsvAs+rgwIDAQABAoIBAAXEGQ674pnMcqAZ
YCoHqY/MZFcWbdH+zbn8BFbtsZt5qA5qcYn/rhp4UQ2F3M9VNzkpvGdLCYWZjqmM
CHn0w1VyPmldWZM3CNkuSmCRTyrtQv1py1zYDYMVOAi1l6MBd9jek4fOYuZBBYgt
9W17grT86onVef24byVM9vVkIwGdrSU7BfPv5lLQ1ZdFERm/K3Zolxnk56vqYxSY
/SQlxQHhjpysd2ZnFoTI1f20GFV3puwUVeuMjplOqpGzujWqnoLpaF2RAj5rhmno
9CMk9r6XKATp9Extmr7I4gon+mMxXQQ71tNpwnKT1OJUeGiMyn3rSHb03UqO2NW/
jQ/kDrECgYEA4zXBX0s3S13XTfEeIr41Xl/pZAranb3yoCXEx9V3njWjr/g1/mys
MPbjBmpJyaKNaa79AkmCcVxgBCZQcyl0HZfIty/KDjWkyrlGifIwsa2+b4TBiTBV
dIzR3KUi7a9STyxQ0ACLaMwP4Pjqq7a42qrZ4mRxrOEDTOxloO2oQ+0CgYEA2JD2
Ui3ZZgBy/lc+Y9OpoQngyvqA60jp3WlWp0r/+b8Bd8hlKfraUKMpWzPOcd0ri20I
WF4eh8ehQd1KUnpo4p9yVlKEaKY4LXOJT5erfcDvWM4zenP978MmMoXknjhDB3yN
pjISnFzCUWqowU0LWd2SK5owftxvhgIdwChHny8CgYAIUSxt75Prl1jNSpk+zlWn
yb0CY39TLOPXxuooIoFJck82ntBjKtk2xaLT1kozLlcc2kH6tnQm8Cm3nStuu8K7
XpYDVqV/1ajdnuCdJhdwOZrx/BsViYZnSMxZ1lWr+7H4ofenVY3E3UtrgdEdTggA
QSbOqbybhnUXeMPTmz+AlQKBgQCQy8uFgpY6Ns6r81UJlblyrJIZ5IdRyPxkR0vi
qqifKPAoUFojSU+yXoUGL4s2YjP9Erdjrfonune8++H3Z1S9QPJHeQCCn7Ar43XJ
O5UzjU36SFPu6aK9LfNaEF+c0e0vEj37Aruiw/pASVnvdD3EpfpXIM6P437FUKDn
m4bXjQKBgA4f+kEQJu7xTe7I3P752a4AYEtJtp1Ft72PZHbYqajzBf7yMFTGlBg7
xtbDOoFetXEo0kq9G4yRD7I/rNkD8t38ltIxj+Q2xajsoiu7b0mjVgNWpPWWJtPh
zUm5YABw2lpcM12lpROoS214O5MqvfjuqaIrg6r7v/At4bhT//oT
-----END RSA PRIVATE KEY-----",

    //异步通知地址
    'notify_url'           => "http://外网可访问网关地址/alipay.trade.page.pay-PHP-UTF-8/notify_url.php",

    //同步跳转
    'return_url'           => "http://外网可访问网关地址/alipay.trade.page.pay-PHP-UTF-8/return_url.php",

    //编码格式
    'charset'              => "UTF-8",

    //签名方式
    'sign_type'            => "RSA2",

    //支付宝网关
    'gatewayUrl'           => "https://openapi.alipaydev.com/gateway.do", //https://openapi.alipay.com/gateway.do

    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    'alipay_public_key'    => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAzU87Mxq3Qw2dR6gitDVG8ppagKlotyjVutiUX0+qP92cYSj4mPI2mI9XMO2WjRiOfbJpFBE7wLnS+/YqyOAosR28W24/WdQTwUYBsOR8jb8ixqXKFjfs79H0zWdUmJtC/1v9rq9FysAggi0Gjx3mJ4ZhWnA4U5pgmRytHBCldCgd/NOBgA9MtdqTnuFYL+x50mM/2apoaVfx+ZCW09dPOmg8OjvgP4MJSVF098tEB5RxHj85d4SVug6gDR/DQ07HWWcD8HENiy9KKU0ucoKbDhUgK+i+7tJ7Fj/lWfcMrNDPKDvP/23HIi2MgLeXnw5Mvr5W1e+972RqVGZf0VWEmQIDAQAB",
);
