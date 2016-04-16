
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
                xmlns:scheme="http://www.w3.org/2001/XMLSchema">
    <xsl:output method="xml"  indent="yes"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="/wsdl:definitions">
        <params>
            <xsl:apply-templates select="wsdl:binding" />
        </params>
    </xsl:template>


    <xsl:template match="wsdl:binding" >
        <xsl:apply-templates select="wsdl:operation" mode="Binding">
            <xsl:with-param name="rest_object"><?= $call ?></xsl:with-param>
            <xsl:with-param name="rest_http_method"><?= $httpMethod ?></xsl:with-param>
        </xsl:apply-templates>
    </xsl:template>

    <xsl:template match="wsdl:operation" mode="Binding" >
        <xsl:param name="rest_object"></xsl:param>
        <xsl:param name="rest_http_method"></xsl:param>
        <xsl:if test="wsdl:documentation">
            <xsl:if test="wsdl:documentation/wsdl:rest/@call = $rest_object and wsdl:documentation/wsdl:rest/@httpMethod = $rest_http_method">
                <xsl:variable name="operation_name"><xsl:value-of select="@name"/></xsl:variable>
                <xsl:variable name="message"><xsl:value-of select="substring-after(../../wsdl:portType/wsdl:operation[@name = $operation_name]/wsdl:input/@message, 'tns:')"/></xsl:variable>
                <xsl:variable name="request_type"><xsl:value-of select="substring-after(../../wsdl:message[@name = $message]/wsdl:part[@name = 'params']/@element, 'ns:')"/></xsl:variable>
                <!-- FOR cmoplex type with sequence -->
                <xsl:for-each select="../../wsdl:types/scheme:schema/scheme:element[@name = $request_type]/scheme:complexType/scheme:sequence/scheme:element">
                    <param>

                        <xsl:choose>
                            <xsl:when test="@type">
                                <type><xsl:value-of select="substring-after(@type, 'xs:')"/></type>
                            </xsl:when>
                            <xsl:when test="scheme:simpleType/scheme:restriction">
                                <type><xsl:value-of select="substring-after(scheme:simpleType/scheme:restriction/@base, 'xs:')"/></type>
                                <xsl:if test="substring-after(scheme:simpleType/scheme:restriction/@base, 'xs:') = 'NMTOKEN'">
                                    <enum>
                                        <xsl:for-each select="scheme:simpleType/scheme:restriction/scheme:enumeration">
                                            <item><xsl:value-of select="@value"/></item>
                                        </xsl:for-each>
                                        <!-- добавляем значение по умолчанию -->
                                        <xsl:if test="not(@minOccurs) or @minOccurs = 0">
                                            <item></item>
                                        </xsl:if>
                                    </enum>
                                </xsl:if>
                            </xsl:when>
                        </xsl:choose>

                        <name><xsl:value-of select="@name"/></name>
                        <xsl:choose>
                            <xsl:when test="@minOccurs and @minOccurs = 1">
                                <required>1</required>
                            </xsl:when>
                            <xsl:otherwise>
                                <required>0</required>
                            </xsl:otherwise>
                        </xsl:choose>
                    </param>
                </xsl:for-each>
                <!-- FOR cmoplex type without sequence, but all -->
                <xsl:for-each select="../../wsdl:types/scheme:schema/scheme:element[@name = $request_type]/scheme:complexType/scheme:all/scheme:element">
                    <param>

                        <xsl:choose>
                            <xsl:when test="@type">
                                <type><xsl:value-of select="substring-after(@type, 'xs:')"/></type>
                            </xsl:when>
                            <xsl:when test="scheme:simpleType/scheme:restriction">
                                <type><xsl:value-of select="substring-after(scheme:simpleType/scheme:restriction/@base, 'xs:')"/></type>
                                <xsl:if test="substring-after(scheme:simpleType/scheme:restriction/@base, 'xs:') = 'NMTOKEN'">
                                    <enum>
                                        <xsl:for-each select="scheme:simpleType/scheme:restriction/scheme:enumeration">
                                            <item><xsl:value-of select="@value"/></item>
                                        </xsl:for-each>
                                        <!-- добавляем значение по умолчанию -->
                                        <xsl:if test="not(@minOccurs) or @minOccurs = 0">
                                            <item></item>
                                        </xsl:if>
                                    </enum>
                                </xsl:if>
                            </xsl:when>
                        </xsl:choose>

                        <name><xsl:value-of select="@name"/></name>
                        <xsl:choose>
                            <xsl:when test="@minOccurs and @minOccurs = 1">
                                <required>1</required>
                            </xsl:when>
                            <xsl:otherwise>
                                <required>0</required>
                            </xsl:otherwise>
                        </xsl:choose>
                    </param>
                </xsl:for-each>
            </xsl:if>
        </xsl:if>
    </xsl:template>


</xsl:stylesheet>