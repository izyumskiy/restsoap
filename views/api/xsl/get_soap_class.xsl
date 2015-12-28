
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
                xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/">
    <xsl:output method="xml"  indent="yes"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="/wsdl:definitions">
        <root>
            <php_class>
                <xsl:value-of select="substring-before(wsdl:service/@name, 'Service')"/>
            </php_class>
            <uri>
                <xsl:value-of select="substring-before(wsdl:service/wsdl:port/soap:address/@location, '?')"/>
            </uri>
        </root>
    </xsl:template>

</xsl:stylesheet>