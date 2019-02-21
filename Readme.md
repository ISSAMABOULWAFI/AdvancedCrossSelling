# Advanced Cross Selling
 


[Advanced cross selling]  Technical Documentation
Technical
VERSION : 1.0


Issam Aboulwafi 







Table of contents


Introduction
Technical Environment
Hooks
Backoffice configuration
















Introduction
Cross-selling is the action or practice of selling an additional product or service to an existing customer. 

The module provides  a solution to add products to the order page in order to being purchased by the customers whom are at the stage of making the payment for there existing cart .

The Solution helps to grow the business as it leads to a higher conversion rate .
The decision to add products to  the order page was based on an A/B test  using only one product with a static image , that helped to  generate a quite good interest .

Technical Environment
 
The module run under prestashop version 1.6.14 and above so it will have no problem in the future if  the  version of prestashop is upgraded.

The module uses existing database tables  such as :
Configuration table to store  data of the module so that we  don’t have to create a table to only  store  some digits and boolean data.
	The module Executse to  type of  Queries depending on the choice made by the manager :
If the manager chose to use the static mode  the module  will run this  query :



If the manager chose to use the dynamique  mode  the module  will run this  query :
(at first it gets the current cart products)


Hooks
In order  to perform what  the  module is meant for  we have to register some hooks :

header
shoppingCart
displayBackOfficeHeader

Backoffice configuration
The backoffice of the module have 2 different options in order to display products in order page 
Static mode  : the static mode  let you choose by yourself the  products that you want to display in the order page . it has a switch button to enable or  disable it .

Dynamique mode : the dynamique mode  at first it gets the products of the customer in his cart that  it  extracts the  default categories of those products in order  to get similar products  also it  JOINS  data to product_sale table if order  to get the best products from those within the same  category of those  who’re on the  customers  cart .


 
