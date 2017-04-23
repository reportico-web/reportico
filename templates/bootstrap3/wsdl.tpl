<?xml version="1.0" encoding="utf-8"?>
<definitions xmlns:soap12="http://schemas.xmlsoap.org/wsdl/soap12/" 
			xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" 
			xmlns:s="http://www.w3.org/2001/XMLSchema" 
			xmlns:s0="http://{$WS_SERVICE_NAMESPACE}/xsd" 
			xmlns:http="http://schemas.xmlsoap.org/wsdl/http/" 
			xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" 
			xmlns:tns="http://{$WS_SERVICE_NAMESPACE}" 
			xmlns:tm="http://microsoft.com/wsdl/mime/textMatching/" 
			xmlns:mime="http://schemas.xmlsoap.org/wsdl/mime/" 
			targetNamespace="http://{$WS_SERVICE_NAMESPACE}"
 			xmlns="http://schemas.xmlsoap.org/wsdl/">

	<types>
		<s:schema elementFormDefault="qualified" targetNamespace="http://{$WS_SERVICE_NAMESPACE}/xsd">
		<s:import namespace="http://{$WS_SERVICE_NAMESPACE}" />
		<s:element name="{$WS_SERVICE_CODE}" type="s0:ReportRequestType" />
		<s:element name="{$WS_SERVICE_CODE}Return" type="s0:ReportDeliveryType" />

		<s:complexType name="ReportRequestType">
			<s:sequence>
{section name=critno loop=$CRITERIA_ITEMS}
				<s:element minOccurs="0" maxOccurs="1" form="unqualified" name="{$CRITERIA_ITEMS[critno].name}" 								type="s:string"/>
{/section}
			</s:sequence>
		</s:complexType>
		<s:complexType name="ReportDeliveryType">
			<s:sequence>
				<s:element minOccurs="1" maxOccurs="1" form="unqualified" name="ReportTitle" type="s:string"/>
				<s:element minOccurs="1" maxOccurs="1" form="unqualified" name="ReportTime" type="s:string"/>
				<s:element minOccurs="0" maxOccurs="1" form="unqualified" name="ReportDelivery" type="s0:ReportLine"/>
			</s:sequence>
		</s:complexType>

		<s:complexType name="ReportLine">
			<s:sequence>
				<s:element minOccurs="0" maxOccurs="1" form="unqualified" name="LineNumber" type="s:int"/>
{section name=columnno loop=$COLUMN_ITEMS}
				<s:element minOccurs="0" maxOccurs="1" form="unqualified" name="{$COLUMN_ITEMS[columnno].name}" type="s:string"/>
{/section}
			</s:sequence>
		</s:complexType>
		</s:schema>
	 </types>

	<message name="{$WS_SERVICE_CODE}SoapIn">
		<part name="{$WS_SERVICE_CODE}" element="s0:{$WS_SERVICE_CODE}" />
	</message>
	<message name="{$WS_SERVICE_CODE}SoapOut">
		<part name="{$WS_SERVICE_CODE}Result" element="s0:{$WS_SERVICE_CODE}Return" />
	</message>

	<portType name="{$WS_SERVICE_CODE}Soap">
		<operation name="{$WS_SERVICE_CODE}">
			<input message="tns:{$WS_SERVICE_CODE}SoapIn" />
			<output message="tns:{$WS_SERVICE_CODE}SoapOut" />
		</operation>
	</portType>

	<binding name="{$WS_SERVICE_CODE}Soap" type="tns:{$WS_SERVICE_CODE}Soap">
		<operation name="{$WS_SERVICE_CODE}">
			<soap:operation soapAction="http://{$WS_SERVICE_NAMESPACE}/" style="document" />
			<input>
				<soap:body use="literal" />
			</input>
			<output>
				<soap:body use="literal" />
			</output>
		</operation>
	</binding>
	<service name="{$WS_SERVICE_NAME}">
		<port name="{$WS_SERVICE_CODE}Soap" binding="tns:{$WS_SERVICE_CODE}Soap">
			<soap:address location="{$WS_SERVICE_BASEURL}/projects/{$PROJECT}/{$WS_SERVICE_CODE}_wsv.php" />
		</port>
	</service>
</definitions>

