
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/">
    <xsl:output method="xml"  indent="yes"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="/wsdl:definitions">
        <root>
            <xsl:apply-templates select="wsdl:binding" >
                <xsl:with-param name="class"><xsl:value-of select="substring-before(wsdl:service/@name, 'Service')"/></xsl:with-param>
            </xsl:apply-templates>
        </root>
    </xsl:template>


    <xsl:template match="wsdl:binding" >
        <xsl:param name="class"></xsl:param>
        <xsl:apply-templates select="wsdl:operation">
            <xsl:with-param name="class"><xsl:value-of select="$class"/></xsl:with-param>
        </xsl:apply-templates>
    </xsl:template>

    <xsl:template match="wsdl:operation" >
        <xsl:param name="class"></xsl:param>
        <xsl:if test="wsdl:documentation">
            <rest>
                <xsl:attribute name="call"><xsl:value-of select="wsdl:documentation/wsdl:rest/@call"/></xsl:attribute>
                <xsl:attribute name="http_method"><xsl:value-of select="wsdl:documentation/wsdl:rest/@httpMethod"/></xsl:attribute>
                <xsl:attribute name="class"><xsl:value-of select="$class"/></xsl:attribute>
                <xsl:attribute name="method"><xsl:value-of select="@name"/></xsl:attribute>
            </rest>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>