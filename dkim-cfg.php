<?
/***************************************************************************\
*  DKIM-CFG ($Id: dkim-cfg-dist.php,v 1.2 2008/09/30 10:21:52 evyncke Exp $)
*  
*  Copyright (c) 2008 
*  Eric Vyncke
*          
* This program is a free software distributed under GNU/GPL licence.
* See also the file GPL.html
*
* THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR 
* IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
* OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
* IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
* INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
* NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
* DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
* THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
*THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 ***************************************************************************/
 
// Uncomment the $open_SSL_pub and $open_SSL_priv variables and
// copy and paste the content of the public- and private-key files INCLUDING
// the first and last lines (those starting with ----)

$open_SSL_pub="-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDV6WW7XLQ8a6haKzFEb+YnXK7P
m0wDSGpQHnEWQ+wpsRjiX2SL/lYI+ATqj4b8p2kCflQas2Pjnec2YnxgPcl2Sk8K
HjL/zdQvILEEz4oXmYTI7Q05Q8TVlDEslfIF42ghBQm/84Zcx8IOPcBGlHS4eBXU
adUbw3jTxKSwm/eVYQIDAQAB
-----END PUBLIC KEY-----";

$open_SSL_priv="-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDV6WW7XLQ8a6haKzFEb+YnXK7Pm0wDSGpQHnEWQ+wpsRjiX2SL
/lYI+ATqj4b8p2kCflQas2Pjnec2YnxgPcl2Sk8KHjL/zdQvILEEz4oXmYTI7Q05
Q8TVlDEslfIF42ghBQm/84Zcx8IOPcBGlHS4eBXUadUbw3jTxKSwm/eVYQIDAQAB
AoGAXyvWbUPTlMY5QtTQuKgod/7Ob+OiMBu54SeKHOPfYLVAYwcaDwb7dEYUud9d
qW86UzetZ6vEl3KbeiDbQV58EOpJjvOIgfYdogGtnvuugVL0/m+tEKAJNhxzjWEz
+l3luLIIywg4mSDXb03R9VqkPfypU2xY5vmO564ns7Qp0BkCQQD6q1X2vtYXzsnJ
bwTnAN/sWb8Pn+8sKlxZwZDsoATyJqJv6niJ1R4ma2NdeEjeCyjzvqDEIQzJE4o2
DL7I3iEfAkEA2nXzSduKSGaRr0TxnvvWS9DAvcWBdxfq11nD+K1nmKrYpbhsb9vn
A2LIQnr9uY8daEldNqd5a3ZcoodsILL5fwJBANhLm4OsK8SjVI8R0uMZaB7jWe+7
i39KliGE2u6zLVFdcPCtG5Gjab6xDy6KKiYe7xlTthlGg2fGCo6U9NMSiPUCQAbj
33d94B+mdIPVpdVA1iJwBBQ4LXwnGfYO07p9JZ5QDSM07N6eTevyaqSGIoh+tgu6
/KCjqZW1FvjdUpC5dFsCQD9nwqz1cPjXj9B4+daBRGgzMf+3SeOC3Ko4oxVFFwJV
JPBubz3vA8zxZ75a2Tp6mEdZYSSxpR9mxzyj3jg/vu8=
-----END RSA PRIVATE KEY-----";

// DKIM Configuration

// Domain of the signing entity (i.e. the email domain)
// This field is mandatory
$DKIM_d='kbnielsen.dk' ;  

// Default identity 
// Optional (can be left commented out), defaults to no user @$DKIM_d
//$DKIM_i='@example.com' ; 

// Selector, defines where the public key is stored in the DNS
//    $DKIM_s._domainkey.$DKIM_d
// Mandatory
$DKIM_s='dkim' ;

?>