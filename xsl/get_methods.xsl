
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/">
    <xsl:output method="xml"  indent="yes"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="/wsdl:definitions">
        <root>
            <xsl:apply-templates select="wsdl:binding" />
        </root>
    </xsl:template>


    <xsl:template match="wsdl:binding" >
        <xsl:apply-templates select="wsdl:operation"/>
    </xsl:template>

    <xsl:template match="wsdl:operation" >
        <method><xsl:value-of select="@name"/></method>
    </xsl:template>

</xsl:stylesheet>