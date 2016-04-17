
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
                xmlns:scheme="http://www.w3.org/2001/XMLSchema"
                xmlns:xs="http://www.w3.org/2001/XMLSchema"
                extension-element-prefixes="wsdl scheme xs">
    <xsl:output method="html"  indent="yes" doctype-system="about:legacy-compat"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="/wsdl:definitions">
        <html>
            <head>
                <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
                <meta charset="utf-8"/>
                <title>Test API</title>
                <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                <meta name="description" content=""/>
                <meta name="author" content=""/>

                <!-- Le styles -->
                <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet"/>
                <style type="text/css">
                    body {
                    padding-top: 60px;
                    padding-bottom: 40px;
                    }
                    .sidebar-nav {
                    padding: 9px 0;
                    }

                </style>
                <link href="/css/bootstrap/bootstrap-responsive.css" rel="stylesheet"/>


                <!-- Fav and touch icons -->
                <link rel="apple-touch-icon-precomposed" sizes="144x144" href="http://getbootstrap.com/2.3.2/assets/ico/apple-touch-icon-144-precomposed.png"/>
                <link rel="apple-touch-icon-precomposed" sizes="114x114" href="http://getbootstrap.com/2.3.2/assets/ico/apple-touch-icon-114-precomposed.png"/>
                <link rel="apple-touch-icon-precomposed" sizes="72x72" href="http://getbootstrap.com/2.3.2/assets/ico/apple-touch-icon-72-precomposed.png"/>
                <link rel="apple-touch-icon-precomposed" href="http://getbootstrap.com/2.3.2/assets/ico/apple-touch-icon-57-precomposed.png"/>
                <link rel="shortcut icon" href="http://getbootstrap.com/2.3.2/assets/ico/favicon.png"/>
            </head>
            <body>
                <xsl:apply-templates select="wsdl:binding" />
            </body>
        </html>
    </xsl:template>


    <xsl:template match="wsdl:binding" >

        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container-fluid">

                    <a class="brand" href="#">Test API</a>
                    <!--div class="nav-collapse collapse">
                      <ul class="nav">
                        <li class="active"><a href="#">Home</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#contact">Contact</a></li>
                      </ul>
                    </div--><!--/.nav-collapse -->
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span3">
                    <div class="well sidebar-nav">
                        <ul class="nav nav-list">
                            <li class="nav-header">Index</li>
                            <xsl:apply-templates select="wsdl:operation" mode="methodList"/>
                        </ul>
                    </div><!--/.well -->
                </div><!--/span-->


                <div class="span9">
                    <xsl:apply-templates select="wsdl:operation" mode="methodDescription"/>
                </div><!--/span-->
            </div><!--/row-->

            <hr/>

            <footer>
                <!--p>© 2015</p-->
            </footer>

        </div><!--/.fluid-container-->

        <!-- Le javascript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="/css/bootstrap/jquery.js"></script>
        <script src="/css/bootstrap/bootstrap-transition.js"></script>
        <script src="/css/bootstrap/bootstrap-alert.js"></script>
        <script src="/css/bootstrap/bootstrap-modal.js"></script>
        <script src="/css/bootstrap/bootstrap-dropdown.js"></script>
        <script src="/css/bootstrap/bootstrap-scrollspy.js"></script>
        <script src="/css/bootstrap/bootstrap-tab.js"></script>
        <script src="/css/bootstrap/bootstrap-tooltip.js"></script>
        <script src="/css/bootstrap/bootstrap-popover.js"></script>
        <script src="/css/bootstrap/bootstrap-button.js"></script>
        <script src="/css/bootstrap/bootstrap-collapse.js"></script>
        <script src="/css/bootstrap/bootstrap-carousel.js"></script>
        <script src="/css/bootstrap/bootstrap-typeahead.js"></script>

    </xsl:template>

    <xsl:template match="wsdl:operation" mode="methodDescription">
        <xsl:if test="wsdl:documentation">

            <div class="row-fluid">
                <div class="span12">
                    <xsl:attribute name="id">
                        <xsl:value-of select="wsdl:documentation/wsdl:rest/@httpMethod"/><xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/>
                    </xsl:attribute>
                    <hr/>
                </div>
                <div class="span12">
                    <xsl:if test="wsdl:documentation/wsdl:status != 'works'">
                        <span>
                            <xsl:attribute name="class">label label-important</xsl:attribute>
                            <xsl:text>Method is under construction</xsl:text>
                        </span>
                    </xsl:if>
                    <h4>
                        <xsl:value-of select="wsdl:documentation/wsdl:rest/@httpMethod"/><xsl:text><![CDATA[ ]]></xsl:text>
                        <xsl:text>/api/rest/example/xml/</xsl:text>
                        <xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/>
                        <xsl:apply-templates select="current()/." mode="methodParams">
                            <xsl:with-param name="rest_object"><xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/></xsl:with-param>
                        </xsl:apply-templates>
                    </h4>
                    <div class="span12">
                        <p><strong>Description:</strong></p>
                        <p><xsl:value-of select="wsdl:documentation/wsdl:text/text()"/></p>
                    </div>
                    <div class="span12">
                        <p><strong>Example:</strong></p>
                        <p>
                            <xsl:apply-templates select="current()/." mode="methodExample">
                                <xsl:with-param name="rest_object"><xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/></xsl:with-param>
                            </xsl:apply-templates>
                        </p>
                    </div>
                    <div class="span4">
                        <p><strong>Input parameters:</strong></p>
                        <p>
                            <xsl:choose>
                                <xsl:when test="wsdl:documentation/wsdl:rest/@httpMethod = 'GET'">
                                    <xsl:apply-templates select="current()/." mode="InputParams">
                                        <xsl:with-param name="rest_object"><xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/></xsl:with-param>
                                    </xsl:apply-templates>
                                </xsl:when>
                                <xsl:when test="wsdl:documentation/wsdl:rest/@httpMethod = 'PUT'">
                                    <xsl:apply-templates select="current()/." mode="Request">
                                        <xsl:with-param name="rest_object"><xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/></xsl:with-param>
                                    </xsl:apply-templates>
                                </xsl:when>
                                <xsl:when test="wsdl:documentation/wsdl:rest/@httpMethod = 'POST'">
                                    <xsl:apply-templates select="current()/." mode="Request">
                                        <xsl:with-param name="rest_object"><xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/></xsl:with-param>
                                    </xsl:apply-templates>
                                </xsl:when>
                            </xsl:choose>
                        </p>
                    </div>
                    <div class="span4">
                        <p><strong>Response:</strong></p>
                        <p>
                            <xsl:apply-templates select="current()/." mode="Response">
                                <xsl:with-param name="rest_object"><xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/></xsl:with-param>
                            </xsl:apply-templates>
                        </p>
                    </div>

                </div><!--/span-->
            </div><!--/row-->

        </xsl:if>
    </xsl:template>

    <xsl:template match="wsdl:operation" mode="Request">
        <xsl:variable name="method"><xsl:value-of select="@name"/></xsl:variable>
        <xsl:variable name="output"><xsl:value-of select="substring-after(../../wsdl:portType/wsdl:operation[@name = $method]/wsdl:input/@message, 'tns:')"/></xsl:variable>
        <xsl:variable name="message"><xsl:value-of select="substring-after(../../wsdl:message[@name = $output]/wsdl:part/@element, 'ns:')"/></xsl:variable>



        <ul class="unstyled">
            <xsl:apply-templates select="../../wsdl:types/xs:schema/xs:element[@name = $message]">
                <xsl:with-param name="count">1</xsl:with-param>
            </xsl:apply-templates>
        </ul>

    </xsl:template>

    <xsl:template match="wsdl:operation" mode="Response">
        <xsl:variable name="method"><xsl:value-of select="@name"/></xsl:variable>
        <xsl:variable name="output"><xsl:value-of select="substring-after(../../wsdl:portType/wsdl:operation[@name = $method]/wsdl:output/@message, 'tns:')"/></xsl:variable>
        <xsl:variable name="message"><xsl:value-of select="substring-after(../../wsdl:message[@name = $output]/wsdl:part/@element, 'ns:')"/></xsl:variable>



        <ul class="unstyled">
            <xsl:apply-templates select="../../wsdl:types/xs:schema/xs:element[@name = $message]">
                <xsl:with-param name="count">1</xsl:with-param>
            </xsl:apply-templates>
        </ul>

    </xsl:template>


    <xsl:template match="xs:element">
        <xsl:param name="count"></xsl:param>

        <li>
            <xsl:text>&lt;</xsl:text>
            <xsl:value-of select="@name"/>
            <xsl:text>&gt;</xsl:text>


            <xsl:if test="@type">
                <xsl:choose>
                    <xsl:when test="contains(@type, 'xs:')">
                        <small class="text-info">
                            <xsl:value-of select="substring-after(@type, 'xs:')"/>
                        </small>
                    </xsl:when>
                    <xsl:when test="contains(@type, 'ns:')">
                        <xsl:variable name="complexType"><xsl:value-of select="substring-after(@type, 'ns:')"/></xsl:variable>

                        <xsl:if test="$count &lt; 4">
                            <ul style="list-style-type:none;">
                                <xsl:apply-templates select="//xs:complexType[@name = $complexType]/xs:sequence/xs:element">
                                    <xsl:with-param name="count"><xsl:value-of select="$count + 1"/></xsl:with-param>
                                </xsl:apply-templates>
                            </ul>
                        </xsl:if>
                    </xsl:when>
                </xsl:choose>
            </xsl:if>



            <xsl:if test="xs:complexType/xs:sequence/xs:group">
                <xsl:variable name="groupName"><xsl:value-of select="substring-after(xs:complexType/xs:sequence/xs:group/@ref, 'ns:')"/></xsl:variable>
                <ul style="list-style-type:none;">
                    <xsl:apply-templates select="../xs:group[@name = $groupName]"/>
                </ul>
            </xsl:if>



            <xsl:if test="not(@type)">
                <ul style="list-style-type:none;">

                    <xsl:if test="xs:simpleType/xs:restriction/@base = 'xs:NMTOKEN'">
                        <small class="text-info">
                            <xsl:apply-templates select="xs:simpleType/xs:restriction/xs:enumeration"/>
                        </small>
                    </xsl:if>

                    <xsl:if test="xs:complexType/xs:sequence/xs:element">

                        <xsl:if test="not(xs:complexType/xs:sequence/xs:element/@type)">
                            <li>
                                <xsl:if test="$count &lt; 4">
                                    <xsl:apply-templates select="xs:complexType/xs:sequence/xs:element">
                                        <xsl:with-param name="count"><xsl:value-of select="$count + 1"/></xsl:with-param>
                                    </xsl:apply-templates>
                                </xsl:if>
                            </li>
                        </xsl:if>

                        <xsl:if test="contains(xs:complexType/xs:sequence/xs:element/@type, 'xs:')">
                            <li>
                                <xsl:apply-templates select="xs:complexType/xs:sequence/xs:element">
                                    <xsl:with-param name="count"><xsl:value-of select="$count + 1"/></xsl:with-param>
                                </xsl:apply-templates>
                            </li>
                        </xsl:if>

                        <xsl:if test="contains(xs:complexType/xs:sequence/xs:element/@type, 'ns:')">
                            <xsl:variable name="complexType"><xsl:value-of select="substring-after(xs:complexType/xs:sequence/xs:element/@type, 'ns:')"/></xsl:variable>
                            <li>
                                <xsl:text>&lt;</xsl:text>
                                <xsl:value-of select="xs:complexType/xs:sequence/xs:element/@name"/>
                                <xsl:text>&gt;</xsl:text>

                                <xsl:if test="$count &lt; 4">
                                    <ul style="list-style-type:none;">
                                        <xsl:apply-templates select="//xs:complexType[@name = $complexType]/xs:sequence/xs:element">
                                            <xsl:with-param name="count"><xsl:value-of select="$count + 1"/></xsl:with-param>
                                        </xsl:apply-templates>
                                    </ul>
                                </xsl:if>

                                <xsl:text>&lt;/</xsl:text>
                                <xsl:value-of select="xs:complexType/xs:sequence/xs:element/@name"/>
                                <xsl:text>&gt;</xsl:text>
                            </li>
                        </xsl:if>

                    </xsl:if>
                </ul>
            </xsl:if>

            <xsl:text>&lt;/</xsl:text>
            <xsl:value-of select="@name"/>
            <xsl:text>&gt;</xsl:text>
        </li>

    </xsl:template>

    <xsl:template match="xs:group">
        <xsl:if test="xs:sequence/xs:element">
            <xsl:apply-templates select="xs:sequence/xs:element">
                <xsl:with-param name="count">1</xsl:with-param>
            </xsl:apply-templates>
        </xsl:if>
    </xsl:template>

    <xsl:template match="xs:enumeration">
        <xsl:value-of select="@value"/><xsl:text>|</xsl:text>
    </xsl:template>


    <xsl:template match="wsdl:operation" mode="methodList">
        <xsl:if test="wsdl:documentation">

            <li>
                <a>
                    <xsl:attribute name="href">


                        <xsl:text>#</xsl:text>
                        <xsl:value-of select="wsdl:documentation/wsdl:rest/@httpMethod"/>
                        <xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/>
                    </xsl:attribute>
                    <xsl:value-of select="wsdl:documentation/wsdl:rest/@httpMethod"/>
                    <xsl:text> /api/rest/example/xml/</xsl:text>
                    <xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/>
                </a>
            </li>

        </xsl:if>

    </xsl:template>

    <xsl:template match="wsdl:operation" mode="methodParams" >
        <xsl:param name="rest_object"></xsl:param>
        <xsl:if test="wsdl:documentation">
            <xsl:if test="wsdl:documentation/wsdl:rest/@call = $rest_object">
                <xsl:if test="wsdl:documentation/wsdl:rest/@httpMethod = 'GET'">
                    <xsl:variable name="operation_name"><xsl:value-of select="@name"/></xsl:variable>
                    <xsl:variable name="message"><xsl:value-of select="substring-after(../../wsdl:portType/wsdl:operation[@name = $operation_name]/wsdl:input/@message, 'tns:')"/></xsl:variable>

                    <xsl:variable name="request_type"><xsl:value-of select="substring-after(../../wsdl:message[@name = $message]/wsdl:part[@name = 'params']/@element, 'ns:')"/></xsl:variable>
                    <xsl:for-each select="../../wsdl:types/scheme:schema/scheme:element[@name = $request_type]/scheme:complexType/scheme:sequence/scheme:element">
                        <xsl:text>/{</xsl:text>
                        <xsl:value-of select="@name"/>
                        <xsl:text>}</xsl:text>
                    </xsl:for-each>
                </xsl:if>
            </xsl:if>
        </xsl:if>
    </xsl:template>

    <xsl:template match="wsdl:operation" mode="methodDefaultParams" >
        <xsl:param name="rest_object"></xsl:param>
        <xsl:if test="wsdl:documentation">
            <xsl:if test="wsdl:documentation/wsdl:rest/@call = $rest_object">
                <xsl:if test="wsdl:documentation/wsdl:rest/@httpMethod = 'GET'">
                    <xsl:variable name="operation_name"><xsl:value-of select="@name"/></xsl:variable>
                    <xsl:variable name="message"><xsl:value-of select="substring-after(../../wsdl:portType/wsdl:operation[@name = $operation_name]/wsdl:input/@message, 'tns:')"/></xsl:variable>

                    <xsl:variable name="request_type"><xsl:value-of select="substring-after(../../wsdl:message[@name = $message]/wsdl:part[@name = 'params']/@element, 'ns:')"/></xsl:variable>
                    <xsl:for-each select="../../wsdl:types/scheme:schema/scheme:element[@name = $request_type]/scheme:complexType/scheme:sequence/scheme:element">
                        <xsl:if test="@default">
                            <xsl:if test="@minOccurs = 1">
                                <xsl:text>/</xsl:text><xsl:value-of select="@default"/>
                            </xsl:if>
                        </xsl:if>
                    </xsl:for-each>
                </xsl:if>
            </xsl:if>
        </xsl:if>
    </xsl:template>

    <xsl:template match="wsdl:operation" mode="methodExample">
        <xsl:param name="rest_object"></xsl:param>
        <xsl:if test="wsdl:documentation/wsdl:rest/@call = $rest_object">
            <a>
                <xsl:attribute name="href">
                    <xsl:text>http://restsoap.com/api/rest/example/json/</xsl:text>
                    <xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/>
                    <xsl:apply-templates select="current()/." mode="methodDefaultParams">
                        <xsl:with-param name="rest_object"><xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/></xsl:with-param>
                    </xsl:apply-templates>
                    <xsl:text>?key=123456</xsl:text>
                </xsl:attribute>
                <xsl:attribute name="target">
                    <xsl:text>blank</xsl:text>
                </xsl:attribute>
                <xsl:text>http://restsoap.com/api/rest/example/json/</xsl:text>
                <xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/>
                <xsl:apply-templates select="current()/." mode="methodDefaultParams">
                    <xsl:with-param name="rest_object"><xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/></xsl:with-param>
                </xsl:apply-templates>
                <xsl:text></xsl:text>
            </a>
        </xsl:if>

    </xsl:template>

    <xsl:template match="wsdl:operation" mode="Binding" >
        <xsl:param name="rest_object"></xsl:param>
        <xsl:if test="wsdl:documentation">
            <xsl:if test="wsdl:documentation/wsdl:rest/@call = $rest_object">
                <xsl:if test="wsdl:documentation/wsdl:rest/@httpMethod = 'GET'">
                    <xsl:variable name="operation_name"><xsl:value-of select="@name"/></xsl:variable>
                    <xsl:variable name="message"><xsl:value-of select="substring-after(../../wsdl:portType/wsdl:operation[@name = $operation_name]/wsdl:input/@message, 'tns:')"/></xsl:variable>
                    <!-- Эту часть возможно придется скорректировать в зависимости от конечного вида WSDL-ки -->
                    <xsl:variable name="request_type"><xsl:value-of select="substring-after(../../wsdl:message[@name = $message]/wsdl:part[@name = 'params']/@element, 'ns:')"/></xsl:variable>
                    <xsl:for-each select="../../wsdl:types/scheme:schema/scheme:element[@name = $request_type]/scheme:complexType/scheme:sequence/scheme:element">
                        <xsl:text>/{</xsl:text>
                        <xsl:if test="substring-after(@type, 'xs:') != ''">
                            <xsl:text>(</xsl:text>
                            <xsl:value-of select="substring-after(@type, 'xs:')"/>
                            <xsl:text>)</xsl:text>
                        </xsl:if>
                        <xsl:value-of select="@name"/>
                        <xsl:text>}</xsl:text>
                    </xsl:for-each>
                    <!--xsl:text></xsl:text-->
                </xsl:if>
            </xsl:if>
        </xsl:if>
    </xsl:template>

    <xsl:template match="wsdl:operation" mode="Operations" >
        <xsl:if test="wsdl:documentation">

            <div>
                <xsl:attribute name="style"><xsl:text>margin-top:10px</xsl:text></xsl:attribute>
                <xsl:if test="wsdl:documentation/wsdl:status/text() = 'works'">
                    <xsl:text>+ </xsl:text>
                </xsl:if>
                <xsl:if test="wsdl:documentation/wsdl:status/text() != 'works'">
                    <xsl:text>- </xsl:text>
                </xsl:if>
                <xsl:value-of select="wsdl:documentation/wsdl:rest/@httpMethod"/><xsl:text> </xsl:text>
                <a>
                    <xsl:attribute name="href">
                        <xsl:text>/api/rest/example/xml/</xsl:text>
                        <xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/>
                        <xsl:apply-templates select="current()/." mode="Binding">
                            <xsl:with-param name="rest_object"><xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/></xsl:with-param>
                        </xsl:apply-templates>
                    </xsl:attribute>
                    <xsl:text>/api/rest/example/xml/</xsl:text>
                    <xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/>
                    <xsl:apply-templates select="current()/." mode="Binding">
                        <xsl:with-param name="rest_object"><xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/></xsl:with-param>
                    </xsl:apply-templates>
                </a>
                <xsl:text> </xsl:text>
                <div>
                    <xsl:attribute name="style"><xsl:text>font-family:sans-serif;color:blue;font-style:italic</xsl:text></xsl:attribute>
                    <xsl:value-of select="wsdl:documentation/wsdl:text/text()"/>
                </div>
                <div>
                    <xsl:apply-templates select="current()/." mode="InputParams">
                        <xsl:with-param name="rest_object"><xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/></xsl:with-param>
                    </xsl:apply-templates>
                </div>
            </div>

        </xsl:if>
    </xsl:template>

    <xsl:template match="wsdl:operation" mode="InputParams" >
        <xsl:param name="rest_object"></xsl:param>
        <xsl:if test="wsdl:documentation">
            <xsl:if test="wsdl:documentation/wsdl:rest/@call = $rest_object">
                <xsl:if test="wsdl:documentation/wsdl:rest/@httpMethod = 'GET'">
                    <xsl:variable name="operation_name"><xsl:value-of select="@name"/></xsl:variable>
                    <xsl:variable name="message"><xsl:value-of select="substring-after(../../wsdl:portType/wsdl:operation[@name = $operation_name]/wsdl:input/@message, 'tns:')"/></xsl:variable>
                    <xsl:variable name="request_type"><xsl:value-of select="substring-after(../../wsdl:message[@name = $message]/wsdl:part[@name = 'params']/@element, 'ns:')"/></xsl:variable>
                    <xsl:if test="count(../../wsdl:types/scheme:schema/scheme:element[@name = $request_type]/scheme:complexType/scheme:sequence/scheme:element) > 0">
                        <ul class="unstyled">
                            <xsl:for-each select="../../wsdl:types/scheme:schema/scheme:element[@name = $request_type]/scheme:complexType/scheme:sequence/scheme:element">
                                <xsl:apply-templates select="current()/." mode="list" />
                            </xsl:for-each>
                        </ul>
                    </xsl:if>
                </xsl:if>
            </xsl:if>
        </xsl:if>
    </xsl:template>

    <xsl:template match="scheme:element" mode="list" >
        <li>
            <small class="text-info">
                <xsl:choose>
                    <xsl:when test="@type">
                        <xsl:value-of select="substring-after(@type, 'xs:')"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>enum</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </small>
            <xsl:text> </xsl:text>
            <xsl:value-of select="@name"/>
            <xsl:if test="@minOccurs = 1"><small class="text-error"> required</small></xsl:if>
            <xsl:if test="not(@type)">
                <ul class="inline">
                    <li><small>possible values: </small></li>
                    <xsl:for-each select="scheme:simpleType/scheme:restriction/scheme:enumeration">
                        <li><small class="text-success"><xsl:value-of select="@value"/><xsl:text>,</xsl:text></small></li>
                    </xsl:for-each>
                </ul>
            </xsl:if>

        </li>
    </xsl:template>


</xsl:stylesheet>