<!-- Копирование XSD-схемы из WSDL для последующей валидации ответов методов API -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
                xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xsl:output method="html"  indent="yes"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="/wsdl:definitions">
        <xsl:apply-templates select="wsdl:types/xs:schema" />
    </xsl:template>


    <xsl:template match="xs:schema">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="@targetNamespace">
        <xsl:attribute name="attr"><xsl:value-of select="."/></xsl:attribute>
    </xsl:template>

    <xsl:template match="@type">
        <xsl:variable name="val"><xsl:value-of select="."/></xsl:variable>
        <xsl:if test="contains($val, 'ns:')">
            <xsl:attribute name="type"><xsl:value-of select="substring-after($val, 'ns:')"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="not(contains($val, 'ns:'))">
            <xsl:attribute name="type"><xsl:value-of select="."/></xsl:attribute>
        </xsl:if>
    </xsl:template>

    <xsl:template match="@ref">
        <xsl:variable name="val"><xsl:value-of select="."/></xsl:variable>
        <xsl:if test="contains($val, 'ns:')">
            <xsl:attribute name="ref"><xsl:value-of select="substring-after($val, 'ns:')"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="not(contains($val, 'ns:'))">
            <xsl:attribute name="ref"><xsl:value-of select="."/></xsl:attribute>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>